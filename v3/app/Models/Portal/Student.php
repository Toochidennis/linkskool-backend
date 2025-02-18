<?php

namespace V3\App\Models\Portal;

use PDO;
use V3\App\Utilities\QueryExecutor;

class Student extends QueryExecutor
{
    private string $table;

    /**
     * Constructor for the Student model.
     *
     * @param PDO $pdo A PDO instance connected to the desired database.
     *                 This connection will be passed to the parent QueryExecutor
     *                 to facilitate database interactions.
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table = 'students_record';
    }

    /**
     * Inserts a new student record into the database.
     *
     * @param array $data An associative array where the keys are column names
     *                    and the values are the corresponding values to insert
     *                    into the 'students_record' table.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function insertStudent(array $data): bool
    {
        return parent::insert($this->table, $data);
    }

    /**
     * Updates existing student records in the database.
     *
     * @param array $data       An associative array of columns and their new values
     *                          to update in the 'students_record' table.
     * @param array $conditions An associative array specifying the conditions
     *                          to identify which records to update. The keys are
     *                          column names, and the values are the values those
     *                          columns must match for a record to be updated.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function updateStudent(array $data, array $conditions): bool
    {
        return parent::update($this->table, $data, $conditions);
    }

    /**
     * Retrieves student records from the database.
     *
     * @param array $columns    (Optional) An array of column names to retrieve.
     *                          If empty, all columns will be retrieved.
     * @param array $conditions (Optional) An associative array specifying the conditions
     *                          to filter the results. The keys are column names, and
     *                          the values are the values those columns must match.
     * @param int   $limit      (Optional) The maximum number of records to retrieve.
     *                          Defaults to 0, which means no limit.
     *
     * @return array|false Returns an array of matching records, or false on failure.
     */
    public function getStudents(array $columns = [], array $conditions = [], int $limit = 0)
    {
        return parent::findBy($this->table, columns: $columns, conditions: $conditions, limit: $limit);
    }
}
