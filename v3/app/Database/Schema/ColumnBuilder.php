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
        $default = $this->prepareDefault(
            $this->spec['default'] ?? null,
            $this->spec['nullable'] ?? false
        );
        $unique = !empty($this->spec['unique']) ? ' UNIQUE' : '';
        $auto = $this->spec['auto_increment'] ? ' AUTO_INCREMENT' : '';

        return \sprintf(
            "ALTER TABLE `%s` ADD COLUMN `%s` %s %s%s%s%s;",
            $this->table,
            $this->column,
            $type,
            $nullable,
            $default,
            $unique,
            $auto
        );
    }

    private function prepareDefault($default, bool $nullable): string
    {
        if ($default === null) {
            return $nullable ? " DEFAULT NULL" : '';
        }

        $upper = strtoupper(trim((string)$default));
        $sqlFunctions = ['CURRENT_TIMESTAMP', 'NOW()', 'UUID()', 'CURRENT_DATE', 'CURRENT_TIME'];

        if (\in_array($upper, $sqlFunctions, true)) {
            return " DEFAULT $upper";
        }

        $safe = addslashes((string)$default);
        return " DEFAULT '{$safe}'";
    }
}
