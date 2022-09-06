<?php

/** Constructs and executes MySql TRUNCATE queries. */
class StmtTruncate extends AbstractStmt
{
    
    /**
     * Execute the TRUNCATE query.
     *
     * @return integer Number of affected rows.
     */
    public function getResults(): int
    {
        $queryString = 'TRUNCATE ' . $this->databaseString . '.' . $this->tableString;
        $affectedRows = $this->query($this->connection, $queryString)->affected_rows;
        return $affectedRows;
    }

}

?>