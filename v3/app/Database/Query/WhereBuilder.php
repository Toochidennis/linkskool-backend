<?php

namespace V3\App\Database\Query;

class WhereBuilder
{
    private array $conditions = [];
    private array $bindings = [];

    public function where(string $column, $operator, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $quoted = $this->wrap($column);
        $prefix = count($this->conditions) === 0 ? '' : 'AND ';
        $this->conditions[] = "{$prefix}{$quoted} {$operator} ?";
        $this->bindings[] = $value;

        return $this;
    }

    public function orWhere(string $column, $operator, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $quoted = $this->wrap($column);
        $prefix = count($this->conditions) === 0 ? '' : 'OR ';
        $this->conditions[] = "{$prefix}{$quoted} {$operator} ?";
        $this->bindings[] = $value;

        return $this;
    }

    public function getClause(): string
    {
        return '(' . implode(' ', $this->conditions) . ')';
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    private function wrap(string $identifier): string
    {
        $parts = explode('.', $identifier);
        return implode('.', array_map(fn($p) => "`$p`", $parts));
    }
}
