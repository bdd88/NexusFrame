<?php

/** Base class used to create and execute MySql statements/queries. */
abstract class AbstractStmt
{
    protected string $databaseString;
    protected string $tableString;

    public function __construct(protected mysqli $connection, string $databaseName)
    {
        $this->databaseString = $this->encapColumnString($databaseName);
    }

    /** Execute the query and return results. */
    abstract protected function getResults();

    /** Encapsulates a string in backticks. */
    protected function encapColumnString(string $string): string
    {
        return '`' . $string . '`';
    }

    /** Encapsulates a string in single quotes. */
    protected function encapFieldString(string $string): string
    {
        return '\'' . $string . '\'';
    }

    /** Run a query on the database. */
    protected function query(mysqli $connection, string $queryString): mysqli
    {
        $queryResponse = $connection->real_query($queryString);
        if ($queryResponse === FALSE) {
            throw new Exception('Query: ' . $queryString . '\nError: ' . $connection->error);
        }
        return $connection;
    }

    /**
     * Set the table name to perform the query on.
     *
     * @param string $tableName
     * @return $this
     */
    public function table(string $tableName): static
    {
        $this->tableString = $this->encapColumnString($tableName);
        return $this;
    }

}

?>