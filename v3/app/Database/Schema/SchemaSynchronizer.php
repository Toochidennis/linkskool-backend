<?php

namespace V3\App\Database\Schema;

use PDO;

class SchemaSynchronizer
{
    private PDO $pdo;
    private array $schema;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->schema = require __DIR__ . '/schema.php';
    }

    public function sync(string $table): void
    {
        $definition = $this->schema[$table];

        if (!$this->tableExists($table)) {
            $this->createTable($table, $definition);
        } else {
            $this->syncColumns($table, $definition);
        }
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE '$table'");
        return $stmt?->fetchColumn();
    }

    private function createTable(string $table, array $definition): void
    {
        $builder = new TableBuilder($table, $definition);
        $sql = $builder->build();
        $this->pdo->exec($sql);
    }

    private function syncColumns(string $table, array $definition): void
    {
        $stmt = $this->pdo->query("SHOW COLUMNS FROM `$table`");
        $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($definition as $column => $spec) {
            if ($column === '__meta') {
                continue;
            }
            if (!in_array($column, $existing)) {
                $builder = new ColumnBuilder($table, $column, $spec);
                $sql = $builder->build();
                $this->pdo->exec($sql);
            }
        }
    }
}
