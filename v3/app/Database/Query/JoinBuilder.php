<?php

/**
 * This class helps handles student's course result
 *
 * PHP version 8.2+
 *
 * @category Query
 * @package Linkskool
 * @author ToochiDennis <dennistoochukwu@gmail.com>
 * @license MIT
 * @link https://www.linkskool.net
 */

namespace V3\App\Database\Query;

use Closure;
use V3\App\Database\Schema\SchemaSynchronizer;

class JoinBuilder
{
    public array $conditions = [];
    public array $bindings = [];

    public function __construct(private SchemaSynchronizer $schemaSynchronizer)
    {
    }

    public function on(string|Closure $left, ?string $operator = null, $right = null): self
    {
        if ($left instanceof Closure) {
            $group = new self($this->schemaSynchronizer);
            $left($group);
            $clause = '(' . $group->getClause() . ')';
            $this->conditions[] = ['AND', $clause];
            $this->bindings = array_merge($this->bindings, $group->bindings);
        } else {
            $this->syncTablesFromIdentifiers($left, $right);

            $left = $this->wrapIdentifier($left);

            if (\is_string($right) && str_contains($right, '.')) {
                $right = $this->wrapIdentifier($right);
                $this->conditions[] = ['AND', "$left $operator $right"];
            } else {
                if ($right === null && strtolower($operator) === '=') {
                    $this->conditions[] = ['AND', "$left IS NULL"];
                } else {
                    $this->conditions[] = ['AND', "$left $operator ?"];
                    $this->bindings[] = $right;
                }
            }
        }

        return $this;
    }

    public function orOn(string|Closure $left, ?string $operator = null, $right = null): self
    {
        if ($left instanceof Closure) {
            $group = new self($this->schemaSynchronizer);
            $left($group);
            $clause = '(' . $group->getClause() . ')';
            $this->conditions[] = ['OR', $clause];
            $this->bindings = array_merge($this->bindings, $group->bindings);
        } else {
            $this->syncTablesFromIdentifiers($left, $right);
            $left = $this->wrapIdentifier($left);

            if (\is_string($right) && str_contains($right, '.')) {
                $right = $this->wrapIdentifier($right);
                $this->conditions[] = ['OR', "$left $operator $right"];
            } else {
                if ($right === null && strtolower($operator) === '=') {
                    $this->conditions[] = ['OR', "$left IS NULL"];
                } else {
                    $this->conditions[] = ['OR', "$left $operator ?"];
                    $this->bindings[] = $right;
                }
            }
        }

        return $this;
    }

    public function getClause(): string
    {
        if (empty($this->conditions)) {
            return '';
        }

        // Start with first condition as is (no leading AND/OR)
        [$firstType, $firstClause] = array_shift($this->conditions);
        $sql = $firstClause;

        foreach ($this->conditions as [$type, $clause]) {
            $sql .= " $type $clause";
        }

        return $sql;
    }

    private function wrapIdentifier(string $identifier): string
    {
        $parts = explode('.', $identifier);
        return implode('.', array_map(fn($part) => "`$part`", $parts));
    }

    private function syncTablesFromIdentifiers(...$args): void
    {
        if (!$this->schemaSynchronizer) {
            return;
        }

        foreach ($args as $arg) {
            if (\is_string($arg) && str_contains($arg, '.')) {
                $parts = explode('.', $arg);
                $this->validateColumn($parts[1]);
                $this->schemaSynchronizer->sync($parts[0]);
            }
        }
    }

    /**
     * Summary of validateColumn
     * @param string $column
     * @throws \InvalidArgumentException
     * @return void
     */
    private function validateColumn(string $column): void
    {
        if ($column !== '*' && !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name: $column");
        }
    }
}
