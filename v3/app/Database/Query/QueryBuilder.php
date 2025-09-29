<?php

namespace V3\App\Database\Query;

use Closure;
use PDO;
use InvalidArgumentException;
use V3\App\Database\Tables;

/**
 * Class QueryBuilder
 *
 * A utility class for building and executing SQL queries using PDO.
 */
class QueryBuilder
{
    private PDO $pdo;
    private string $table;
    private array $selectColumns = ['*'];
    private array $whereConditions = [];
    private array $whereBindings = [];
    private array $updateBindings = [];
    private array $bindings = [];
    private array $joins = [];
    private string $groupBy = '';
    private array $orderBy = [];
    private string $limit = '';
    private string $offset = '';

    /**
     * Constructor to initialize the PDO instance.
     *
     * @param PDO $pdo The PDO database connection instance.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Sets the table for the query.
     *
     * @param  string $table The name of the table.
     * @return self
     */
    public function table(string $table): self
    {
        $this->validateTable($table);
        $this->table = $table;
        return $this;
    }

    /**
     * Specifies the columns to select in a query.
     *
     * @param  array $columns The columns to select.
     * @return self
     */
    public function select(array $columns = ['*']): self
    {
        $this->selectColumns = $columns;
        return $this;
    }

    /**
     * Adds a WHERE condition to the query.
     *
     * @param  string     $column   The column name.
     * @param  mixed      $operator The comparison operator or value if omitted.
     * @param  mixed|null $value    The value to compare against.
     * @return self
     */
    public function where(string|Closure $column, $operator = null, $value = null): self
    {
        if ($column instanceof Closure) {
            $builder = new WhereBuilder();
            $column($builder);
            $this->whereConditions[] = $builder->getClause();
            $this->whereBindings = array_merge($this->whereBindings, $builder->getBindings());
        } else {
            if (func_num_args() === 2) {
                $value = $operator;
                $operator = '=';
            }

            $quoted = $this->wrapIdentifier($column);
            $this->whereConditions[] = "$quoted $operator ?";
            $this->whereBindings[] = $value;
        }

        return $this;
    }

    public function whereGroup(array $conditions): self
    {
        $builder = new WhereBuilder();

        foreach ($conditions as [$column, $operator, $value]) {
            $builder->where($column, $operator, $value);
        }

        $this->whereConditions[] = $builder->getClause();
        $this->whereBindings = array_merge($this->whereBindings, $builder->getBindings());

        return $this;
    }

    public function whereNotInComposite(array $columns, array $pairs): self
    {
        if (empty($columns)) {
            throw new InvalidArgumentException("Columns for NOT IN composite cannot be empty.");
        }
        if (empty($pairs)) {
            throw new InvalidArgumentException("Values for NOT IN composite cannot be empty.");
        }

        // Wrap column names with backticks
        $wrappedCols = array_map(fn($col) => $this->wrapIdentifier($col), $columns);
        $colList = '(' . implode(', ', $wrappedCols) . ')';

        // Generate placeholders for each pair
        $placeholders = [];
        foreach ($pairs as $pair) {
            if (!is_array($pair) || count($pair) !== count($columns)) {
                throw new InvalidArgumentException("Each pair must have exactly " . count($columns) . " values.");
            }
            $placeholders[] = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
            $this->whereBindings = array_merge($this->whereBindings, array_values($pair));
        }

        $this->whereConditions[] = "$colList NOT IN (" . implode(', ', $placeholders) . ")";

        return $this;
    }


    /**
     * Adds one or more ORDER BY clauses to the query.
     *
     * @param  string|array $columns   The column or array of column => direction pairs.
     * @param  string|null  $direction The direction for a single column (ASC or DESC).
     * @return self
     */
    public function orderBy(string|array $columns, ?string $direction = 'ASC'): self
    {
        if (is_array($columns)) {
            foreach ($columns as $column => $dir) {
                $this->orderBy[] = $this->wrapIdentifier($column) . ' ' . strtoupper($dir);
            }
        } else {
            $this->orderBy[] = $this->wrapIdentifier($columns) . ' ' . strtoupper($direction);
        }

        return $this;
    }

    /**
     * Adds a GROUP BY clause to the query.
     *
     * @param  string|array $columns The column(s) to group by.
     * @return self
     */
    public function groupBy(string|array $columns): self
    {
        if (is_array($columns)) {
            $wrapped = array_map(fn($col) => $this->wrapIdentifier($col), $columns);
            $this->groupBy = "GROUP BY " . implode(', ', $wrapped);
        } else {
            $this->groupBy = "GROUP BY " . $this->wrapIdentifier($columns);
        }

        return $this;
    }

    /**
     * Limits the number of rows returned by the query.
     *
     * @param  int $limit The number of rows to return.
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = "LIMIT $limit";
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = "OFFSET $offset";
        return $this;
    }

    /**
     * Adds a JOIN clause to the query.
     *
     * @param  string $table     The table to join.
     * @param  string $condition The join condition.
     * @param  string $type      The type of join (INNER, LEFT, RIGHT, etc.).
     * @return self
     */
    public function join(string $table, Closure|string $condition, string $type = 'INNER'): self
    {
        if ($condition instanceof Closure) {
            $joinBuilder = new JoinBuilder();
            $condition($joinBuilder);

            $onClause = $joinBuilder->getClause();
            $this->bindings = array_merge($this->bindings, $joinBuilder->bindings);
        } else {
            $onClause = $condition;
        }

        $this->joins[] = "$type JOIN `$table` ON $onClause";
        return $this;
    }

    /**
     * Executes the built query and returns the result set as an array.
     *
     * @return array The fetched records.
     */
    public function get(): array
    {
        $columns = empty($this->selectColumns) ? '*' : implode(", ", $this->selectColumns);
        $query = "SELECT $columns FROM `$this->table`";

        if (!empty($this->joins)) {
            $query .= " " . implode(" ", $this->joins);
        }
        if (!empty($this->whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $this->whereConditions);
        }
        if ($this->groupBy) {
            $query .= " " . $this->groupBy;
        }
        if (!empty($this->orderBy)) {
            $query .= " ORDER BY " . implode(', ', $this->orderBy);
        }
        if ($this->limit) {
            $query .= " " . $this->limit;
        }
        if ($this->offset) {
            $query .= " " . $this->offset;
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_merge($this->bindings, $this->whereBindings));
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->reset();
        return $result;
    }

    /**
     * Returns the first row of the result set.
     *
     * @return array The first record
     */
    public function first(): array
    {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? [];
    }

    /**
     * Inserts data into the table.
     *
     * @param  array $data The data to insert.
     * @return int|false The last inserted ID or false on failure.
     */
    public function insert(array $data)
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $stmt = $this->pdo->prepare("INSERT INTO `$this->table` ($columns) VALUES ($placeholders)");

        $this->reset();
        return $stmt->execute(array_values($data)) ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Updates records in the table.
     *
     * @param  array $data The columns and values to update.
     * @return bool True on success, false otherwise.
     */
    public function update(array $data): bool
    {
        if (empty($this->whereConditions)) {
            throw new InvalidArgumentException("Update requires at least one WHERE condition.");
        }

        $setClauses = [];
        foreach ($data as $column => $value) {
            $setClauses[] = "`$column` = ?";
            $this->updateBindings[] = $value;
        }

        $query = "UPDATE `$this->table` SET " .
            implode(", ", $setClauses) .
            " WHERE " .
            implode(" AND ", $this->whereConditions);
        $stmt = $this->pdo->prepare($query);

        $allBindings = array_merge($this->updateBindings, $this->whereBindings);
        $success = $stmt->execute($allBindings);

        $this->reset();

        return $success ? true : false;
    }

    public function notIn(string $column, array $values): self
    {
        if (empty($values)) {
            throw new InvalidArgumentException("Values for NOT IN cannot be empty.");
        }

        $placeholders = implode(", ", array_fill(0, count($values), "?"));
        $this->whereConditions[] = "`$column` NOT IN ($placeholders)";
        $this->whereBindings = array_merge($this->whereBindings, $values);

        return $this;
    }
    public function in(string $column, array $values): self
    {
        if (empty($values)) {
            throw new InvalidArgumentException("Values for IN cannot be empty.");
        }

        $placeholders = implode(", ", array_fill(0, count($values), "?"));
        $this->whereConditions[] = "`$column` IN ($placeholders)";
        $this->whereBindings = array_merge($this->whereBindings, $values);

        return $this;
    }

    public function whereBetween(string $column, $start, $end): self
    {
        $quoted = $this->wrapIdentifier($column);
        $this->whereConditions[] = "$quoted BETWEEN ? AND ?";
        $this->whereBindings[] = $start;
        $this->whereBindings[] = $end;

        return $this;
    }

    public function whereNotBetween(string $column, $start, $end): self
    {
        $quoted = $this->wrapIdentifier($column);
        $this->whereConditions[] = "$quoted NOT BETWEEN ? AND ?";
        $this->whereBindings[] = $start;
        $this->whereBindings[] = $end;

        return $this;
    }

    public function whereNull(string $column): self
    {
        $quoted = $this->wrapIdentifier($column);
        $this->whereConditions[] = "$quoted IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $quoted = $this->wrapIdentifier($column);
        $this->whereConditions[] = "$quoted IS NOT NULL";
        return $this;
    }

    public function whereRaw(string $expression, array $bindings = []): self
    {
        $this->whereConditions[] = $expression;
        $this->whereBindings = array_merge($this->whereBindings, $bindings);

        return $this;
    }

    /**
     * Deletes records from the table.
     *
     * @return bool True on success, false otherwise.
     */
    public function delete(): int|bool
    {
        if (empty($this->whereConditions)) {
            throw new InvalidArgumentException("No conditions provided for deletion. Refusing to delete all records.");
        }

        $query = "DELETE FROM `$this->table` WHERE " . implode(" AND ", $this->whereConditions);

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($this->whereBindings);
        $affectedRows = $stmt->rowCount();

        $this->reset();
        return $affectedRows > 0 ? $affectedRows : false;
    }

    /**
     * Counts the number of rows in the specified table with optional conditions.
     *
     * @return int The total count of rows matching the conditions.
     */
    public function count(): int
    {
        $query = "SELECT COUNT(*) FROM `$this->table`";
        if (!empty($this->whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $this->whereConditions);
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($this->whereBindings);
        $count = $stmt->fetchColumn();

        $this->reset();
        return $count;
    }

    /**
     * Checks if any rows exist in the table matching the given conditions.
     *
     * @return bool True if at least one row exists, otherwise false.
     */
    public function exists(): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM `$this->table`";
        if (!empty($this->whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $this->whereConditions);
        }
        $query .= ")";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($this->whereBindings);
        $exists =  (bool) $stmt->fetchColumn();

        $this->reset();
        return $exists;
    }

    public function paginate(int $page = 1, int $limit = 20): array
    {
        if ($page <= 0 || $limit <= 0) {
            return [];
        }

        $offset = ($page - 1) * $limit;

        $data = $this->limit($limit)->offset($offset)->get();
        $total = $this->count();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($total / $limit),
                'has_next' => $page * $limit < $total,
                'has_prev' => $page > 1
            ],
        ];
    }

    /**
     * Executes a raw SQL query with optional parameter bindings.
     *
     * @param  string $query    The raw SQL query to execute.
     * @param  array  $bindings An array of values to bind to the query parameters.
     * @return array The result set as an associative array.
     */
    public function rawQuery(string $query, array $bindings = []): array
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validates whether the provided table name is allowed.
     *
     * @param  string $table The name of the table to validate.
     * @throws InvalidArgumentException If the table is not allowed.
     */
    private function validateTable(string $table): void
    {
        if (!in_array($table, Tables::ALLOWED_TABLES)) {
            throw new InvalidArgumentException("Request not allowed for table $table");
        }
    }

    private function wrapIdentifier(string $identifier): string
    {
        $parts = explode('.', $identifier);
        return implode('.', array_map(fn($part) => "`$part`", $parts));
    }

    /**
     * Reset variables
     * @return void
     */
    private function reset(): void
    {
        $this->selectColumns = ['*'];
        $this->whereConditions = [];
        $this->whereBindings = [];
        $this->updateBindings = [];
        $this->bindings = [];
        $this->joins = [];
        $this->groupBy = '';
        $this->orderBy = [];
        $this->limit = '';
        $this->offset = '';
    }
}
