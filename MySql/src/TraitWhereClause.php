<?php

/** Trait used by statement classes that implement the MySql WHERE clause. */
trait TraitWhereClause
{
    private array $matches;
    private string $selectString;

    /**
     * Add a new match condition, which will be used to generate the where clause.
     * Operator details:
     * - isEqual/notEqual - (string) Find rows that match/don't match the value.
     * - isEqual/notEqual - (null) Find rows that have a null/not null value.
     * - isEqual/notEqual - (array) Find rows containing/not containing any value in the array.
     * - isLike/notLike - (string) Find rows that match/don't match the wildcard pattern.
     * - isBetween/notBetween - (array) Find rows that match a value within the specified range.
     *
     * @param string $column The column to search when matching values.
     * @param string $operator Must be one of the following: isEqual, notEqual, isLike, notLike, isBetween, notBetween
     * @param string|array|null $value The value search for.
     * @return $this
     */
    public function addMatch(string $column, string $operator, string|array|null $value): static
    {
        // Check the supplied arguments for data type issues.
        $valueType = gettype($value);
        $supportedDataTypes = array(
            'isEqual' => ['string', 'NULL', 'array'],
            'notEqual' => ['string', 'NULL', 'array'],
            'isLike' => ['string'],
            'notLike' => ['string'],
            'isBetween' => ['array'],
            'notBetween' => ['array']
        );
        if (!in_array($valueType, $supportedDataTypes[$operator])) {
            throw new Exception('The ' . $operator . ' operator does not support ' . gettype($value) . ' values. Supported value data types are: ' . implode(', ', $supportedDataTypes[$operator]));
        }

        // Determine the correct match string to create based on the operator and value datatype.
        switch (gettype($value)) {
            case 'string':
                if ($operator === 'isEqual' || $operator === 'notEqual') {
                    $matchString = $this->matchLiteral($column, $operator, $value);
                } elseif ($operator === 'isLike' || $operator === 'notLike') {
                    $matchString = $this->matchPattern($column, $operator, $value);
                }
                break;
            case 'NULL':
                $matchString = $this->matchNull($column, $operator);
                break;
            case 'array':
                if ($operator === 'isEqual' || $operator === 'notEqual') {
                    $matchString = $this->matchIn($column, $operator, $value);
                } elseif ($operator === 'isBetween' || $operator === 'notBetween') {
                    $matchString = $this->matchBetween($column, $operator, $value);
                }
                break;
        }
        
        // Add the new match string to the array of matches to use.
        $this->matches[] = '(' . $matchString . ')';
        return $this;
    }

    /** Use match conditions to construct the WHERE string. */
    private function constructWhereString(array $matches): string
    {
        $whereString = 'WHERE ';
        $lastKey = array_key_last($matches);
        foreach($matches as $key => $match) {
            $whereString .= $match;
            if ($key !== $lastKey) {
                $whereString .= ' AND ';
            }
        }
        return $whereString;
    }

    /** Create a string for matching an exact value. */
    private function matchLiteral(string $column, string $operator, string $value): string
    {
        $operator = ($operator === 'isEqual')? '=' : '!=';
        return $this->encapColumnString($column) . ' ' . $operator . ' ' . $this->encapFieldString($value);
    }

    /** Create a string for matching any similar values. */
    private function matchPattern(string $column, string $operator, string $value): string
    {
        $operator = ($operator === 'isLike')? 'LIKE' : 'NOT LIKE';
        return $this->encapColumnString($column) . ' ' . $operator . ' ' . $this->encapFieldString($value);
    }

    /** Create a string for matching values with/without the NULL datatype. */
    private function matchNull(string $column, string $operator): string
    {
        $operator = ($operator === 'isEqual')? 'IS NULL' : 'IS NOT NULL';
        return $this->encapColumnString($column) . ' ' . $operator;
    }

    /** Create a string for matching any value in a list of values. */
    private function matchIn(string $column, string $operator, array $value): string
    {
        $operator = ($operator === 'isEqual')? 'IN' : 'NOT IN';
        $inString = '';
        $lastKey = array_key_last($value);
        foreach ($value as $key => $value) {
            $inString .= $this->encapFieldString($value);
            if ($key !== $lastKey) {
                $inString .= ', ';
            }

        }
        $matchString = $this->encapColumnString($column) . ' ' . $operator . ' (' . $inString . ')';
        return $matchString;
    }

    /** Create a string for matching values that are within a range of values. */
    private function matchBetween(string $column, string $operator, array $value): string
    {
        if (sizeof($value) !== 2) {
            throw new Exception('There must be exactly two items (range start and range stop) in the value array for the ' . $operator . ' operator.');
        }
        $operator = ($operator === 'isBetween')? 'BETWEEN' : 'NOT BETWEEN';
        return $this->encapColumnString($column) . ' ' . $operator . ' ' . $this->encapFieldString($value[0]) . ' AND ' . $this->encapFieldString($value[1]);
    }

}