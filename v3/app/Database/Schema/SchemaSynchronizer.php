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
            error_log("[SchemaSync] No schema found for table {$table}");
            return;
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
        $sql = str_replace("DEFAULT '0000-00-00 00:00:00'", "DEFAULT NULL", $sql);
        $this->pdo->exec($sql);
    }

    private function syncColumns(string $table, array $definition): void
    {
        $columns = $this->pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $existing = array_column($columns, null, 'Field');

        foreach ($definition as $col => $spec) {
            if ($col === '__meta') {
                continue;
            }

            // New column
            if (!isset($existing[$col])) {
                $this->addColumn($table, $col, $spec);
                continue;
            }

            // Compare & alter drift
            $dbType = strtolower($existing[$col]['Type']);
            $schemaType = strtolower($spec['type']);

            $nullChanged = ($existing[$col]['Null'] === 'NO') !== (!$spec['nullable']);
            $typeChanged = $dbType !== $schemaType;

            if ($nullChanged || $typeChanged) {
                $alter = sprintf(
                    "ALTER TABLE `%s` MODIFY `%s` %s %s",
                    $table,
                    $col,
                    $spec['type'],
                    $spec['nullable'] ? 'NULL' : 'NOT NULL'
                );

                if (isset($spec['default'])) {
                    $default = $spec['default'] ?? null;
                    if ($default === '0000-00-00 00:00:00' || $default === null) {
                        $alter .= " DEFAULT NULL";
                    } else {
                        $alter .= " DEFAULT '{$default}'";
                    }
                }

                $this->pdo->exec($alter);
            }
        }
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
            error_log("[SchemaSync] Add column failed for {$col} on {$table}: {$msg}");
        }
    }
}
