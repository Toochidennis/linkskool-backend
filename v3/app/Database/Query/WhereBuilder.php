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


    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            return $this; // no-op if empty
        }

        $quoted = $this->wrap($column);
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $prefix = count($this->conditions) === 0 ? '' : 'AND ';
        $this->conditions[] = "{$prefix}{$quoted} IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    public function whereNotIn(string $column, array $values): self
    {
        if (empty($values)) {
            return $this;
        }

        $quoted = $this->wrap($column);
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $prefix = count($this->conditions) === 0 ? '' : 'AND ';
        $this->conditions[] = "{$prefix}{$quoted} NOT IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    public function whereBetween(string $column, $start, $end): self
    {
        $quoted = $this->wrap($column);
        $prefix = count($this->conditions) === 0 ? '' : 'AND ';
        $this->conditions[] = "{$prefix}{$quoted} BETWEEN ? AND ?";
        $this->bindings[] = $start;
        $this->bindings[] = $end;

        return $this;
    }

    public function whereNull(string $column): self
    {
        $quoted = $this->wrap($column);
        $prefix = count($this->conditions) === 0 ? '' : 'AND ';
        $this->conditions[] = "{$prefix}{$quoted} IS NULL";

        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $quoted = $this->wrap($column);
        $prefix = count($this->conditions) === 0 ? '' : 'AND ';
        $this->conditions[] = "{$prefix}{$quoted} IS NOT NULL";

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
