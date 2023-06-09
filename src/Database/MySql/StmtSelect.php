<?php
namespace NexusFrame\Database\MySql;

use Exception;
/** Constructs and executes MySql SELECT queries. */
class StmtSelect extends AbstractStmt
{
    use TraitWhereClause;

    private string $columnString;
    private string $columnFuncString;
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
            $columnString .= $this->encapColumnString($columnName);
            if ($key !== $lastKey) $columnString .= ', ';
        }
        $this->columnString = $columnString;
        return $this;
    }

    public function columnFunction(string $function, string $column, ?string $as = NULL): StmtSelect
    {
        $this->verifyMySqlFunction($function);
        $columnFuncString = $function . '(' . $this->encapColumnString($column) . ')';
        if ($as !== NULL) $columnFuncString .= ' AS ' . $this->encapColumnString($as);
        $this->columnFuncString = $columnFuncString;
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
     * @return array An array of arrays representing rows.
     */
    public function getResults(): array
    {
        // Build the query string.
        if (isset($this->columnString) && isset($this->columnFuncString)) {
            $queryString = 'SELECT ' . $this->columnString . ', ' . $this->columnFuncString;
        } elseif (isset($this->columnString)) {
            $queryString = 'SELECT ' . $this->columnString;
        } elseif (isset($this->columnFuncString)) {
            $queryString = 'SELECT ' . $this->columnFuncString;
        } else {
            $queryString = 'SELECT *';
        }
        $queryString .=  ' FROM ' . $this->tableString;
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

        // Run the query and return the results.
        $results = $this->query($this->connection, $queryString)->store_result()->fetch_all(MYSQLI_ASSOC);
        return $results;
    }
}
