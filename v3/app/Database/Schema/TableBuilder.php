<?php

namespace V3\App\Database\Schema;

class TableBuilder
{
    public function __construct(private string $table, private array $definition)
    {
    }

    public function build(): string
    {
        $columns = [];

        foreach ($this->definition as $column => $spec) {
            if ($column === '__meta') {
                continue;
            }

            $colDef = "`$column` {$spec['type']}";
            $colDef .= $spec['nullable'] ? ' NULL' : ' NOT NULL';
            if (isset($spec['default'])) {
                $colDef .= $spec['default'] === null ? ' DEFAULT NULL' : " DEFAULT '{$spec['default']}'";
            }
            if ($spec['auto_increment']) {
                $colDef .= ' AUTO_INCREMENT';
            }
            $columns[] = $colDef;
        }


        $primary = array_keys(array_filter(
            $this->definition,
            fn($d) => isset($d['primary']) && $d['primary'] === true
        ));
        $primarySQL = $primary
            ? ', PRIMARY KEY (`' . implode('`,`', $primary) . '`)'
            : '';


        $meta = $this->definition['__meta'] ?? [];
        $engine = $meta['engine'] ?? 'InnoDB';
        $charset = $meta['charset'] ?? 'utf8mb4';
        $collate = $meta['collate'] ?? 'utf8mb4_unicode_ci';

        return sprintf(
            "CREATE TABLE IF NOT EXISTS `%s` (%s%s) ENGINE=%s DEFAULT CHARSET=%s COLLATE=%s;",
            $this->table,
            implode(',', $columns),
            $primarySQL,
            $engine,
            $charset,
            $collate
        );
    }
}
