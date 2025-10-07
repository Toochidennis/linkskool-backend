<?php

namespace V3\App\Database\Schema;

class ColumnBuilder
{
    public function __construct(private string $table, private string $column, private array $spec)
    {
    }

    public function build(): string
    {
        $sql = "ALTER TABLE `{$this->table}` ADD COLUMN `{$this->column}` {$this->spec['type']}";
        $sql .= $this->spec['nullable'] ? ' NULL' : ' NOT NULL';
        if (isset($this->spec['default'])) {
            $sql .= $this->spec['default'] === null ? ' DEFAULT NULL' : " DEFAULT '{$this->spec['default']}'";
        }
        return "$sql;";
    }
}
