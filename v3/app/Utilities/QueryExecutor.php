<?php

namespace V3\App\Utilities;

class QueryExecutor
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    private function validateTable(string $table)
    {
        #die( $table);

        if (!in_array($table, Tables::ALLOWED_TABLES)) {
            throw new \InvalidArgumentException("Request not allowed for table $table");
        }
    }

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

    public function select(string $table)
    {
        $this->validateTable($table);
        $stmt = $this->pdo->prepare("SELECT * FROM `$table`");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function update(string $table, array $data, array $conditions)
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

    public function delete(string $table, array $conditions)
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

    public function findBy(
        string $table, 
        array $columns = [], 
        array $conditions = [], 
        int $limit = 0)
    {
        $this->validateTable($table);

        // Validate columns
        $columnsList = !empty($columns) ? implode(", ", $columns) : "*";

        // Add conditions if provided
        $whereClause = '';
        if (!empty($conditions)) {
            $whereParts = [];
            foreach ($conditions as $column => $value) {
                $whereParts[] = "`$column` = ?";
            }
            $whereClause = 'WHERE ' . implode(' AND ', $whereParts);
        }

        // Add LIMIT clause if provided
        $limitClause = ($limit !== 0) ? "LIMIT $limit" : '';

        $query = "SELECT $columnsList FROM `$table` $whereClause $limitClause";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($conditions));

        return $limit === 1 ? $stmt->fetch(\PDO::FETCH_ASSOC) : $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


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
                throw new \InvalidArgumentException("Invalid join data at index $index. Each join must include 'table' and 'condition'.");
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
        return $limit === 1 ? $stmt->fetch(\PDO::FETCH_ASSOC) : $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
