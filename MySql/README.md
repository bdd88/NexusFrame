# MySql
A database model for interacting with MySql/MariaDB databases. Arrays are used to build queries in a simple and consistent manner, removing the inconvenience and potential syntax errors in manually writing queries.

## Composer Installation
```
composer require bdd88/mysql
```

## Usage
+ Provide database connection credentials when creating the MySql object, and connections will automatically be handled.
+ Use the supplied methods to perform database queries: create, read, update, delete, truncate
+ Prepared statements can also be run using the 'preparedStatement' method for queries that use end user input.

## Examples
#### Database connection
Create the database connection:
```
$db = new \Bdd88\MySql\MySql('hostname', 'username', 'password', 'database');
```

### INSERT Query
Creates a new row in the specified table with the specified values. Returns the insert ID of the new row.
```
$newRecordId = $db->create('tableName', [
    'someNumber' => 546,
    'someString' => 'lorem ipsum',
    'someNothing' => NULL
]);
```

### SELECT Query
Reads one or more rows from the specified table and returns the results as an array of objects.

#### Table
If only the table name is supplied then the contents of the entire table are returned:
```
$results = $db->read('tableName');
```

#### Columns and functions
Column names can be optionally supplied to limit the results to only columns that are needed, or to perform additional functional operations.  
Supply an indexed array to limit results to specific columns:
```
$results = $db->read('tableName', ['id', 'name', 'etc']);
```
Supply an associative array with 'function', 'column', and (optionally) 'as' keys to execute a MySql function in the query:
```
$results = $db->read(
    'tableName',
    [
        'function' => 'sum',
        'column' => 'amount',
        'as' => 'totalAmount'
    ]
);
```

#### Values
Column values can be optionally supplied to filter results to matching rows.  
Multiple match statements can be supplied (results will match all supplied statements.)  
An indexed array containing three values should be supplied for each match statement: column name, operator, value  
Valid operators are: isEqual, notEqual, isBetween, and notBetween.  
isEqual and notEqual operators can accept an array to create multiple OR conditions for the same column.  
isBetween and notBetween operators can only accept a two item array for $value: start and stop respectively.
```
$results = $db->read(
    'tableName',
    NULL,
    [
        ['id', 'isEqual', 43],
        ['type', 'notEqual', ['apple', 'orange', 'pear']],
        ['expiration', 'isBetween', ['monday', 'friday']],
        ['price', 'notEqual', NULL]
    ]
);
```

#### Sorting
Results can be sorted by mutiple columns.  
Each array entry should be an array containing the column and direction to sort.
```
$results = $db->read(
    'tableName',
    NULL,
    NULL,
    [
        ['date', 'DESC'],
        ['type', 'ASC']
    ]
);
```

### UPDATE Query
Update matching rows in the specified table with the supplied values. Returns the number of affected rows.  
Uses the same syntax for matching as SELECT statements (see the SELECT Query Values section above).
```
$affectedRows = $db->update(
    'transactions',
    [
        ['account_id', 'isEqual', 93],
        ['type', 'isEqual', 'TRADE'],
        ['assetType', 'isEqual', 'EQUITY'],
        ['transactionDate', 'isBetween', ['2021-01-01T00:00:00+0000', '2023-01-01T00:00:00+0000']]
    ],
    [
        'description' => 'Already Recorded'
    ]
);
```

### DELETE Query
Delete rows that match supplied values in the specified table. Returns the number of affected rows.  
Uses the same syntax for matching as SELECT statements (see the SELECT Query Values section above).
```
$affectedRows = $db->update(
    'cars',
    [
        ['type', 'notBetween', ['50', '89']],
        ['color' 'isEqual', 'green']
    ]
);
```

### TRUNCATE Query
Removes an entire table from the database. Returns the number of affected rows.
```
$affectedRows = $db->truncate('tableName');
```

### Prepared Statements
Executes a prepared statement using the supplied values and returns a mysqli_stmt object containing the results.  
Prepared statements should always be used when utilizing data from end users to mitigate the risk of injection attacks.  
The prepared string should use '?' as placeholders for data to be bound.  
The second argument should be an array containing the data types and values to be bound.  
See https://www.php.net/manual/en/mysqli.prepare.php for more information.
```
$results = $db->preparedStatement(
    'SELECT `account_id` FROM `accounts` WHERE `username` = ?',
    array('s', $username)
);
$userCount = $results->num_rows;
```