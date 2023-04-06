<?php

/** Constructs and executes MySql SELECT queries. */
class StmtSelect extends AbstractStmt
{
    use TraitWhereClause;
    
    private string $columnString;
    private string $joinString;
    private array $sorts;
    private string $orderString;
    private string $groupString;

    /**
     * Verify a MySql function is supported.
     *
     * @throws Exception If the function is not supported.
     * @param string $functionName The function name to check for.
     * @return string The supplied function name in uppercase.
     */
    private function verifyMySqlFunction(string $functionName): string
    {
        $functionName = strtoupper($functionName);
        $supportedFunctions = array('AVG', 'COUNT', 'MAX', 'MIN', 'SUM');
        if (!in_array($functionName, $supportedFunctions)) {
            throw new Exception('MySql function is not supported. Supported functions: ' . implode(', ', $supportedFunctions));
        }
        return $functionName;
    }

    private function constructOrderString(array $sorts): string
    {
        $orderString = 'ORDER BY ';
        $lastKey = array_key_last($sorts);
        foreach ($sorts as $key => $sortString) {
            $orderString .= $sortString;
            if ($key !== $lastKey) {
                $orderString .= ', ';
            }
        }
        return $orderString;
    }

    /** Filter results to only include specified columns. If nothing is specified, all columns will be returned. */
    public function columns(...$columnNames): StmtSelect
    {
        $columnString = '';
        $lastKey = array_key_last($columnNames);
        foreach ($columnNames as $key => $columnName) {
            if (is_array($columnName)) {
                $functionName = $this->verifyMySqlFunction(key($columnName));
                $columnString .= $functionName . '(' . $this->encapColumnString(current($columnName)) . ')';
            } else {
                $columnString .= $this->encapColumnString($columnName);
            }
            if ($key !== $lastKey) {
                $columnString .= ', ';
            }
        }
        $this->columnString = $columnString;
        return $this;
    }

    public function join(string $joinType, string $secondTable, string $firstTableColumn, string $secondTableColumn): StmtSelect
    {
        $joinType = strtoupper($joinType);
        $supportedTypes = array('INNER', 'LEFT', 'RIGHT', 'CROSS');
        if (!in_array($joinType, $supportedTypes)) {
            throw new Exception('Join type must be one of the following: inner, left, right, cross, self.');
        }
        $joinString = $joinType . ' JOIN ' . $this->encapColumnString($secondTable);

        if ($joinType !== 'CROSS') {
            $joinString .=  ' ON ' . $this->tableString . '.' . $this->encapColumnString($firstTableColumn) . ' = ' . $this->encapColumnString($secondTable) . '.' . $this->encapColumnString($secondTableColumn);
        }

        $this->joinString = $joinString;
        return $this;
    }

    public function groups(...$columnNames): StmtSelect
    {
        $groupString = 'GROUP BY ';
        $lastKey = array_key_last($columnNames);
        foreach ($columnNames as $key => $columnName) {
            $groupString .= $this->encapColumnString($columnName);
            if ($key !== $lastKey) {
                $groupString .= ', ';
            }
        }
        $this->groupString = $groupString;
        return $this;
    }

    /**
     * Sort results by one or more columns.
     *
     * @param string|array $columnName Name of the column to sort.
     * @param string $direction Either asc or desc
     * @return StmtSelect
     */
    public function addSort(string|array $columnName, string $direction): StmtSelect
    {
        $direction = strtoupper($direction);
        if ($direction !== 'ASC' && $direction !== 'DESC') {
            throw new Exception('Sort direction must be either ASC or DESC.');
        }

        if (gettype($columnName) === 'string') {
            $this->sorts[] = $this->encapColumnString($columnName) . ' ' . $direction; 
        } else {
            $functionName = $this->verifyMySqlFunction(key($columnName));
            $this->sorts[] = $functionName . '(' . $this->encapColumnString(current($columnName)) . ') ' . $direction;
        }
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
        if (isset($this->joinString)) {
            $queryString .= ' ' . $this->joinString;
        }
        if (isset($this->matches)) {
            $queryString .= ' ' . $this->constructWhereString($this->matches);
        }
        if (isset($this->groupString)) {
            $queryString .= ' ' . $this->groupString;
        }
        if (isset($this->sorts)) {
            $queryString .= ' ' . $this->constructOrderString($this->sorts);
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