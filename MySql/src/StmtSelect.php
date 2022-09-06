<?php

/** Constructs and executes MySql SELECT queries. */
class StmtSelect extends AbstractStmt
{
    use TraitWhereClause;
    
    private string $columnString;

    /** Filter results to only include specified columns. If nothing is specified, all columns will be returned. */
    public function columns(array $columnNames): StmtSelect
    {
        $columnString = '';
        $lastKey = array_key_last($columnNames);
        foreach ($columnNames as $key => $columnName) {
            $columnString .= $this->encapColumnString($columnName);
            if ($key !== $lastKey) {
                $columnString .= ', ';
            }
        }
        $this->columnString = $columnString;
        return $this;
    }

    /**
     * Execute the SELECT query.
     *
     * @return object[] An array of objects representing rows.
     */
    public function getResults(): array
    {
        // Build the query string.
        if (isset($this->columnString)) {
            $queryString = 'SELECT ' . $this->columnString . ' FROM ' . $this->tableString;
        } else {
            $queryString = 'SELECT * FROM ' . $this->tableString;
        }
        if (isset($this->matches)) {
            $queryString .= ' ' . $this->constructWhereString($this->matches);
        }

        // Run the query and return the results as an array of objects.
        $results = $this->query($this->connection, $queryString)->store_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($results as &$result) {
            $result = (object) $result;
        }
        return $results;
    }
}

?>