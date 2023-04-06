<?php

/** Constructs and executes MySql UPDATE queries. */
class StmtUpdate extends AbstractStmt
{
    use TraitWhereClause;

    private string $setValuesString;

    /** Set the values to use when updating rows. */
    public function values(array $values): StmtUpdate
    {
        $lastColumn = array_key_last($values);
        $setString = '';
        foreach ($values as $column => $value) {
            $setString .= $this->encapColumnString($column) . ' = ' . $this->encapFieldString($value);
            if ($column !== $lastColumn) {
                $setString .= ', ';
            }
        }
        $this->setValuesString = $setString;
        return $this;
    }

    /**
     * Execute the UPDATE query.
     *
     * @return integer Number of affected rows.
     */
    public function getResults(): int
    {
        $queryString = 'UPDATE ' . $this->tableString . ' SET ' . $this->setValuesString;
        if (isset($this->matches)) {
            $queryString .= ' ' . $this->constructWhereString($this->matches);
        }
        $affectedRows = $this->query($this->connection, $queryString)->affected_rows;
        return $affectedRows;
    }

}

?>