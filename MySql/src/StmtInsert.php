<?php

/** Constructs and executes MySql INSERT queries. */
class StmtInsert extends AbstractStmt
{
    protected string $insertColumnsString;
    protected string $insertValuesString;

    public function values(array $values): StmtInsert
    {
        $lastColumn = array_key_last($values);
        $columnString = '';
        $valueString = '';
        foreach ($values as $column => $value) {
            $columnString .= $this->encapColumnString($column);
            $valueString .= $this->encapFieldString($value);
            if ($column !== $lastColumn) {
                $columnString .= ', ';
                $valueString .= ', ';
            }
        }
        $this->insertColumnsString = '(' . $columnString . ')';
        $this->insertValuesString = '(' . $valueString . ')';
        return $this;
    }

    /**
     * Execute the INSERT query.
     *
     * @return integer The insert ID of the newly created row.
     */
    public function getResults(): int
    {
        $queryString = 'INSERT INTO ' . $this->tableString . ' ' . $this->insertColumnsString . ' VALUES ' . $this->insertValuesString;
        $insertId = $this->query($this->connection, $queryString)->insert_id;
        return $insertId;
    }
}

?>