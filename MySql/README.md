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
### Database connection
Create the database connection:
```
$database = new MySql(new mysqli(), 'hostname', 'username', 'password', 'database');
```

### INSERT Statement
Creates a new row in the specified table with the specified values. Returns the insert ID of the new row.
Methods:
- table
- values
- getResults
***Example***
```
$insertId = $database->create()
    ->table('someTable')
    ->values([
        'test' => 'derp',
        'woot' => 'woop'
    ])
    ->getResults()
;
```

### SELECT Query
Reads one or more rows from the specified table and returns the results as an array of objects.
Methods:
- table
- columns
- join
- addMatch
- groups
- addSort
- getResults
***Example***
```
$results = $database->read()
    ->table('bigTable')
    ->columns('id', 'name', ['sum' => 'city'], 'country')
    ->join('left', 'otherTable', 'id', 'id')
    ->addMatch('id', 'isEqual', 35)
    ->addMatch('name', 'isLike', 'Gar%')
    ->addMatch('color', 'notEqual', ['Red', 'Yellow', 'Purple'])
    ->addMatch('date', 'isBetween', ['monday', 'friday'])
    ->groups('country', 'name')
    ->addSort('name', 'desc')
    ->addSort(['sum' => 'city'], 'asc')
    ->getResults()
;
```

### UPDATE Query
Update matching rows in the specified table with the supplied values. Returns the number of affected rows.
Methods:
- table
- addMatch
- values
- getResults
***Example***
```
$affectedRows = $database->update()
    ->table('smallTable')
    ->addMatch('id', 'isBetween', [1, 25])
    ->values([
        'test' => 'true',
        'desc' => 'testing'
    ])
    ->getResults()
;
```

### DELETE Query
Delete rows that match supplied values in the specified table. Returns the number of affected rows.
Methods:
- table
- addMatch
- getResults
***Example***
```
$affectedRows = $database->delete()
    ->table('thatTable')
    ->addMatch('id', 'isEqual', '5')
    ->getResults()
;
```

### TRUNCATE Query
Removes an entire table from the database. Returns the number of affected rows.
Methods: N/A
***Example***
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