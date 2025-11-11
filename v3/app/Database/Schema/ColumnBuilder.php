<?php

namespace V3\App\Database\Schema;

class ColumnBuilder
{
    public function __construct(
        private string $table,
        private string $column,
        private array $spec
    ) {
    }

    public function build(): string
    {
        $type = strtoupper($this->spec['type']);
        $nullable = $this->spec['nullable'] ? 'NULL' : 'NOT NULL';
        $default = $this->prepareDefault($this->spec['default'] ?? null, $type);
        $auto = $this->spec['auto_increment'] ? ' AUTO_INCREMENT' : '';

        return \sprintf(
            "ALTER TABLE `%s` ADD COLUMN `%s` %s %s%s%s;",
            $this->table,
            $this->column,
            $type,
            $nullable,
            $default,
            $auto
        );
    }

    private function prepareDefault($default, string $type): string
    {
        if (str_contains(strtolower($type), 'datetime') || str_contains(strtolower($type), 'timestamp')) {
            if ($default === '0000-00-00 00:00:00') {
                return ' DEFAULT NULL';
            }
            if ($default === null) {
                return ' DEFAULT NULL';
            }
            return " DEFAULT '{$default}'";
        }

        if ($default === null) {
            return '';
        }
        if ($default === '') {
            return " DEFAULT ''";
        }
        return " DEFAULT '{$default}'";
    }
}
