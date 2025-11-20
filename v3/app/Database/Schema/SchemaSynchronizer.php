<?php

namespace V3\App\Database\Schema;

use PDO;
use PDOException;

class SchemaSynchronizer
{
    private PDO $pdo;
    private array $schema;
    private static array $syncedTables = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
       // $this->pdo->exec("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'STRICT_TRANS_TABLES',''))");
        $this->schema = require __DIR__ . '/schema.php';
    }

    public function sync(string $table): void
    {
        // Skip if already synced in this request lifecycle
        if (isset(self::$syncedTables[$table])) {
            return;
        }
        self::$syncedTables[$table] = true;

        $definition = $this->schema[$table] ?? null;
        if (!$definition) {
            throw new \Exception("Schema definition for table {$table} not found.");
           // return;
        }

        $localTxn = !$this->pdo->inTransaction();
        if ($localTxn) {
            $this->pdo->beginTransaction();
        }

        try {
            if (!$this->tableExists($table)) {
                $this->createTable($table, $definition);
            } else {
                $this->syncColumns($table, $definition);
            }

            if ($localTxn && $this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
        } catch (PDOException $e) {
            if ($localTxn && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            //throw $e;
        }
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE " . $this->pdo->quote($table));
        return (bool) $stmt?->fetchColumn();
    }

    private function createTable(string $table, array $definition): void
    {
        $builder = new TableBuilder($table, $definition);
        $sql = $builder->build();
        $this->pdo->exec($sql);
    }

    private function syncColumns(string $table, array $definition): void
    {
        // Always fetch fresh column info for THIS table only.
        $stmt = $this->pdo->query("SHOW COLUMNS FROM `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Re-index by column name
        $existing = array_column($columns, null, 'Field');

        foreach ($definition as $col => $spec) {
            if ($col === '__meta') {
                continue;
            }

            if (
                str_contains($spec['type'] ?? '', 'datetime')
                || str_contains($spec['type'] ?? '', 'time')
                || str_contains($spec['type'] ?? '', 'date')
            ) {
                $this->cleanupInvalidDates($table, $col);
            }

            // If column doesn't exist, create it
            if (!\array_key_exists($col, $existing)) {
                $this->addColumn($table, $col, $spec);
                continue;
            }

            // Prepare clean comparison values
            $dbType = strtolower($existing[$col]['Type']);
            $schemaType = strtolower($spec['type'] ?? '');
            $dbNull = $existing[$col]['Null'] === 'YES';
            $schemaNull = $spec['nullable'] ?? false;

            $typeChanged = $dbType !== $schemaType;
            $nullChanged = $dbNull !== $schemaNull;

            if ($typeChanged || $nullChanged) {
                $sql = "ALTER TABLE `$table` MODIFY `$col` {$spec['type']}";

                // Nullability
                $sql .= $schemaNull ? " NULL" : " NOT NULL";

                // Default handling
                if (\array_key_exists('default', $spec)) {
                    $default = $spec['default'];

                    if ($default === null) {
                        if ($schemaNull) {
                            $sql .= " DEFAULT NULL";
                        }
                    } else {
                        $upper = strtoupper(trim((string)$default));
                        $sqlFunctions = ['CURRENT_TIMESTAMP', 'NOW()', 'UUID()', 'CURRENT_DATE', 'CURRENT_TIME'];

                        if (\in_array($upper, $sqlFunctions, true)) {
                            $sql .= " DEFAULT $upper";
                        } else {
                            $safe = addslashes((string)$default);
                            $sql .= " DEFAULT '$safe'";
                        }
                    }
                }

                $sql .= !empty($spec['unique']) ? " UNIQUE" : '';

                $sql .= !empty($spec['auto_increment']) ? " AUTO_INCREMENT" : '';

                $this->pdo->exec($sql);
            }
        }
    }

    private function cleanupInvalidDates(string $table, string $column): void
    {
        $this->pdo->exec("
            UPDATE `$table`
            SET `$column` = NULL
            WHERE `$column` LIKE '0000-00-00%'
        ");
    }

    private function addColumn(string $table, string $col, array $spec): void
    {
        try {
            $builder = new ColumnBuilder($table, $col, $spec);
            $sql = $builder->build();
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Duplicate column')) {
                return;
            }
            throw $e;
        }
    }
}
