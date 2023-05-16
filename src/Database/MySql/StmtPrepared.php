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
     * @return array
     */
    public function getResults(): array
    {
        $this->statement->execute();
        $this->statement->bind_result($results);
        $this->statement->fetch();
        if ($this->statement === FALSE) {
            throw new Exception('Prepared statement failed: ' . $this->query . ' Error: ' . $this->connection->error);
        }
        return $results;
    }
}