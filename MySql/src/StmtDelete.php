<?php

/** Constructs and executes MySql DELETE queries. */
class StmtDelete extends AbstractStmt
{
    use TraitWhereClause;
    
    /**
     * Execute the DELETE query.
     *
     * @return integer Number of affected rows.
     */
    public function getResults(): int
    {
        $queryString = 'DELETE FROM ' . $this->tableString;
        if (isset($this->matches)) {
            $queryString .= ' ' . $this->constructWhereString($this->matches);
        }
        $affectedRows = $this->query($this->connection, $queryString)->affected_rows;
        return $affectedRows;
    }

}

?>