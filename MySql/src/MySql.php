<?php

/** Factory for database connection and query objects. */
class MySql
{
    protected mysqli $connection;

    public function __construct(protected string $hostname, protected string $username, protected string $password, protected string $database)
    {
        $this->connect();
    }

    public function __destruct()
    {
        $this->connection->close();
    }

    /** Generate a new database connection object */
    protected function connect(): void
    {
        $this->connection = new mysqli($this->hostname, $this->username, $this->password, $this->database);
        if ($this->connection->connect_error) {
            throw new Exception("Connection failed: " . $this->connection->connect_error);
        }
    }

    /** Generate a new create (INSERT statement) object. */
    public function create(): StmtInsert
    {
        return new StmtInsert($this->connection, $this->database);
    }

    /** Generate a new read (SELECT statement) object. */
    public function read(): StmtSelect
    {
        return new StmtSelect($this->connection, $this->database);
    }

    /** Generate a new update (UPDATE statement) object. */
    public function update(): StmtUpdate
    {
        return new StmtUpdate($this->connection, $this->database);
    }

    /** Generate a new delete (DELETE statement) object. */
    public function delete(): StmtDelete
    {
        return new StmtDelete($this->connection, $this->database);
    }

    /** Generate a new truncate (TRUNCATE statement) object. */
    public function truncate(): StmtTruncate
    {
        return new StmtTruncate($this->connection, $this->database);
    }

}

?>