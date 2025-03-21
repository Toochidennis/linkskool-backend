<?php

namespace V3\App\Utilities;

use PDO;
use InvalidArgumentException;

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
    private array $bindings = [];
    private array $joins = [];
    private string $orderBy = '';
    private string $limit = '';

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
     * @param string $table The name of the table.
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
     * @param array $columns The columns to select.
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
     * @param string $column The column name.
     * @param mixed $operator The comparison operator or value if omitted.
     * @param mixed|null $value The value to compare against.
     * @return self
     */
    public function where(string $column, $operator, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->whereConditions[] = "`$column` $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Adds an ORDER BY clause to the query.
     *
     * @param string $column The column to order by.
     * @param string $direction The sorting direction (ASC or DESC).
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "ORDER BY `$column` $direction";
        return $this;
    }

    /**
     * Limits the number of rows returned by the query.
     *
     * @param int $limit The number of rows to return.
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = "LIMIT $limit";
        return $this;
    }

    /**
     * Adds a JOIN clause to the query.
     *
     * @param string $table The table to join.
     * @param string $condition The join condition.
     * @param string $type The type of join (INNER, LEFT, RIGHT, etc.).
     * @return self
     */
    public function join(string $table, string $condition, string $type = 'INNER'): self
    {
        $this->joins[] = "$type JOIN `$table` ON $condition";
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
        if ($this->orderBy) {
            $query .= " " . $this->orderBy;
        }
        if ($this->limit) {
            $query .= " " . $this->limit;
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($this->bindings);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns the first row of the result set.
     *
     * @return array|null The first record or null if none found.
     */
    public function first(): ?array
    {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }

    /**
     * Inserts data into the table.
     *
     * @param array $data The data to insert.
     * @return int|false The last inserted ID or false on failure.
     */
    public function insert(array $data)
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $stmt = $this->pdo->prepare("INSERT INTO `$this->table` ($columns) VALUES ($placeholders)");

        if ($stmt->execute(array_values($data))) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Updates records in the table.
     *
     * @param array $data The columns and values to update.
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
            $this->bindings[] = $value;
        }

        $query = "UPDATE `$this->table` SET " . implode(", ", $setClauses) . " WHERE " . implode(" AND ", $this->whereConditions);
        $stmt = $this->pdo->prepare($query);

        return $stmt->execute($this->bindings);
    }

    public function notIn(string $column, array $values): self
    {
        if (empty($values)) {
            throw new InvalidArgumentException("Values for NOT IN cannot be empty.");
        }

        $placeholders = implode(", ", array_fill(0, count($values), "?"));
        $this->whereConditions[] = "`$column` NOT IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    /**
     * Deletes records from the table.
     *
     * @return bool True on success, false otherwise.
     */
    public function delete(): bool
    {
        if (empty($this->whereConditions)) {
            throw new InvalidArgumentException("No conditions provided for deletion. Refusing to delete all records.");
        }

        $query = "DELETE FROM `$this->table` WHERE " . implode(" AND ", $this->whereConditions);
        $stmt = $this->pdo->prepare($query);

        return $stmt->execute($this->bindings);
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
        $stmt->execute($this->bindings);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Checks if any rows exist in the table matching the given conditions.
     *
     * @return bool True if at least one row exists, otherwise false.
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Executes a raw SQL query with optional parameter bindings.
     *
     * @param string $query The raw SQL query to execute.
     * @param array $bindings An array of values to bind to the query parameters.
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
     * @param string $table The name of the table to validate.
     * @throws InvalidArgumentException If the table is not allowed.
     */
    private function validateTable(string $table): void
    {
        if (!in_array($table, Tables::ALLOWED_TABLES)) {
            throw new InvalidArgumentException("Request not allowed for table $table");
        }
    }
}
