<?php

namespace V3\App\Models;

use PDO;
use V3\App\Utilities\QueryExecutor;

abstract class Model
{
    protected static string $table;
    protected static PDO $pdo;
    protected static QueryExecutor $queryExecutor;

    public static function setDatabase(PDO $pdo)
    {
        self::$pdo = $pdo;
        self::$queryExecutor = new QueryExecutor($pdo);
    }

    public static function all()
    {
        return self::$queryExecutor->select(static::$table);
    }

    public static function find($id)
    {
        return self::$queryExecutor->findBy(table: static::$table, conditions: ['id' => $id], limit: 1);
    }

    public static function where(array $columns, array $conditions, int $limit = 0)
    {
        return self::$queryExecutor->findBy(
            static::$table,
            $columns,
            $conditions,
            $limit
        );
    }

    public static function create(array $data)
    {
        return self::$queryExecutor->insert(static::$table, $data);
    }

    public static function update(array $data, array $conditions)
    {
        return self::$queryExecutor->update(static::$table, $data, $conditions);
    }

    public static function delete(array $conditions)
    {
        return self::$queryExecutor->deleteRecords(static::$table, $conditions);
    }
}
