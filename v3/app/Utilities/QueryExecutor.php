<?php

namespace V3\App\Utilities;

use PDO;
use InvalidArgumentException;

/**
 * Class QueryExecutor
 *
 * Provides a set of methods to perform common database operations such as
 * insert, select, update, delete, and complex queries with joins.
 */
class QueryExecutor
{
    /**
     * @var PDO The PDO instance for database interaction.
     */
    private PDO $pdo;

    /**
     * QueryExecutor constructor.
     *
     * @param PDO $pdo The PDO instance connected to the database.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Validates if the provided table name is allowed for operations.
     *
     * @param string $table The name of the table to validate.
     * @throws InvalidArgumentException If the table is not in the list of allowed tables.
     */
    private function validateTable(string $table): void
    {
        if (!in_array($table, Tables::ALLOWED_TABLES)) {
            throw new InvalidArgumentException("Request not allowed for table $table");
        }
    }

    /**
     * Inserts a new record into the specified table.
     *
     * @param string $table The name of the table where the record will be inserted.
     * @param array $data An associative array where keys are column names and values are the corresponding values to insert.
     * @return bool|string The ID of the inserted record on success, or false on failure.
     */
    public function insert(string $table, array $data)
    {
        $this->validateTable($table);
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));

        $stmt = $this->pdo->prepare("INSERT INTO `$table` ($columns) VALUES ($placeholders);");
        if ($stmt->execute(array_values($data))) {
            return $this->pdo->lastInsertId();
        }

        return false;
    }

    /**
     * Retrieves all records from the specified table.
     *
     * @param string $table The name of the table to select from.
     * @return array An array of associative arrays representing the fetched records.
     */
    public function select(string $table)
    {
        $this->validateTable($table);
        $stmt = $this->pdo->prepare("SELECT * FROM `$table`");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Updates records in the specified table based on given conditions.
     *
     * @param string $table The name of the table to update.
     * @param array $data An associative array where keys are column names to update and values are the new values.
     * @param array $conditions An associative array where keys are column names and values are the conditions for the update.
     * @return bool True on success, false on failure.
     */
    public function update(string $table, array $data, array $conditions): bool
    {
        $this->validateTable($table);
        $setClauses = [];
        foreach ($data as $column => $value) {
            $setClauses[] = "`$column` = ?";
        }
        $whereClauses = [];
        foreach ($conditions as $column => $value) {
            $whereClauses[] = "`$column` = ?";
        }

        $query = "UPDATE `$table` SET " . implode(", ", $setClauses);
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $stmt = $this->pdo->prepare($query);
        return $stmt->execute(array_merge(array_values($data), array_values($conditions)));
    }

    /**
     * Deletes records from the specified table based on given conditions.
     *
     * @param string $table The name of the table to delete from.
     * @param array $conditions An associative array where keys are column names and values are the conditions for the deletion.
     * @return bool True on success, false on failure.
     */
    public function delete(string $table, array $conditions): bool
    {
        $this->validateTable($table);

        $whereClauses = [];
        foreach ($conditions as $column => $value) {
            $whereClauses[] = "`$column` = ?";
        }

        $query = "DELETE FROM `$table` WHERE " . implode(" AND ", $whereClauses);
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute(array_values($conditions));
    }

    /**
     * Finds records in the specified table based on given conditions.
     *
     * @param string $table The name of the table to search in.
     * @param array $columns An array of column names to retrieve. Defaults to all columns.
     * @param array $conditions An associative array where keys are column names and values are the conditions for the search.
     * @param int $limit The maximum number of records to retrieve. Defaults to 0 (no limit).
     * @return array|false An array of associative arrays representing the fetched records, a single associative array if limit is 1, or false on failure.
     */
    public function findBy(
        string $table,
        array $columns = [],
        array $conditions = [],
        int $limit = 0
    ) {
        $this->validateTable($table);

        $columnsList = !empty($columns) ? implode(", ", $columns) : "*";

        $whereClause = '';
        if (!empty($conditions)) {
            $whereParts = [];
            foreach ($conditions as $column => $value) {
                $whereParts[] = "`$column` = ?";
            }
            $whereClause = 'WHERE ' . implode(' AND ', $whereParts);
        }

        $limitClause = ($limit !== 0) ? "LIMIT $limit" : '';

        $query = "SELECT $columnsList FROM `$table` $whereClause $limitClause";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($conditions));

        return $limit === 1 ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executes a complex query with joins.
     *
     * @param string $table The primary table to query from.
     * @param array $columns An array of column names to retrieve. Defaults to all columns.
     * @param array $joins An array of join definitions, each containing:
     *                     - 'table': The table to join with.
     *                     - 'condition': The join condition.
     *                     - 'type' (optional): The type of join (e.g., 'INNER', 'LEFT'). Defaults to 'INNER'.
     * @param array $conditions An associative array where keys are column names and values are the conditions for the search.
     * @param int $limit The maximum number of records to retrieve. Defaults to 0 (no limit).
     * @return array|bool
     */

    public function queryWithJoins(
        string $table,
        array $columns = [],
        array $joins = [],
        array $conditions = [],
        int $limit = 0
    ) {
        $columnsList = !empty($columns) ? implode(", ", $columns) : "*";

        // Build the FROM and JOIN clauses.
        $query = "SELECT $columnsList FROM `$table`";
        foreach ($joins as $index => $join) {
            if (!isset($join['table'], $join['condition'])) {
                throw new InvalidArgumentException("Invalid join data at index $index. Each join must include 'table' and 'condition'.");
            }
            $joinType = !empty($join['type']) ? $join['type'] : 'INNER';
            $query .= " $joinType JOIN `{$join['table']}` ON {$join['condition']}";
        }

        // Build the WHERE clause.
        $whereParts = [];
        foreach ($conditions as $key => $value) {
            $whereParts[] = "`$key` = ?";
        }
        $whereClause = !empty($whereParts) ? ' WHERE ' . implode(' AND ', $whereParts) : '';

        $limitClause = ($limit !== 0) ? "LIMIT $limit" : '';

        // Combine all parts into the full query.
        $query .= "$whereClause $limitClause";

        // Prepare and execute the query.
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($conditions));

        // Return the result.
        return $limit === 1 ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
