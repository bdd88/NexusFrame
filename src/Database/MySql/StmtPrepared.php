<?php
namespace NexusFrame\Database\MySql;

use mysqli_stmt;
use Exception;

/** Constructs and executes MySql prepared statements. */
class StmtPrepared extends AbstractStmt
{
    private string $query;
    private mysqli_stmt $statement;

    /**
     * Prepare a query statement.
     *
     * @param string $queryString
     * @return StmtPrepared
     */
    public function statement(string $queryString): StmtPrepared
    {
        $this->query = $queryString;
        $this->statement = $this->connection->prepare($queryString);
        return $this;
    }

    /**
     * Bind parameters to the prepared statement.
     *
     * @param array $parameters
     * @return StmtPrepared
     */
    public function parameters(array $parameters): StmtPrepared
    {
        $this->statement->bind_param(...$parameters);
        return $this;
    }

    /**
     * Execute the prepared statement and return the results.
     *
     * @return array|integer An associative array of results (for SELECT and similar) or the number of rows affected (for everything else).
     */
    public function getResults(): array|int
    {
        $this->statement->execute();
        if ($this->statement->errno !== 0) {
            throw new Exception('Prepared statement failed: ' . $this->query . ' Error: ' . $this->statement->error);
        }
        $results = $this->statement->get_result();
        $output = ($results === FALSE) ? $this->statement->affected_rows : $results->fetch_all(MYSQLI_ASSOC);
        return $output;
    }
}