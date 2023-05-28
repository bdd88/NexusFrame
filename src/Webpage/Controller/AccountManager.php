<?php
namespace NexusFrame\Webpage\Controller;

use Exception;
use NexusFrame\Database\MySql\MySql;

/** Handles user account management. */
class AccountManager
{
    public function __construct(
        private MySql $mySql,
        private ?string $table = NULL,
        private ?string $idCol = NULL,
        private ?string $usernameCol = NULL,
        private ?string $hashCol = NULL,
        private ?string $createdCol = NULL,
        private ?string $seenCol = NULL,
        private ?string $ipCol = NULL)
    {
        $this->table ??= 'accounts';
        $this->idCol ??= 'account_id';
        $this->usernameCol ??= 'username';
        $this->hashCol ??= 'hash';
        $this->createdCol ??= 'created';
        $this->seenCol ??= 'lastSeen';
        $this->ipCol ??= 'lastIp';
    }

    /**
     * Create a new user account.
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function create(string $username, string $password): bool
    {
        // Verify the username is available.
        $queryString = 'SELECT `' . $this->idCol . '` FROM `' . $this->table . '` WHERE `' . $this->usernameCol . '` = ?';
        $accounts = $this->mySql->preparedStatement()
            ->statement($queryString)
            ->parameters(array('s', $username))
            ->getResults()
        ;
        if (sizeof($accounts) > 0) return FALSE;

        // Create the new account.
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $created = time();
        $queryString = 'INSERT INTO `' . $this->table . '` (`' . $this->usernameCol . '`, `' . $this->hashCol . '`, `' . $this->createdCol . '`) VALUES (?, ?, ?)';
        $this->mySql->preparedStatement()
            ->statement($queryString)
            ->parameters(array('ssi', $username, $hash, $created))
            ->getResults()
        ;
        return TRUE;
    }

    /**
     * Authenticate a username and password combination.
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function authenticate(string $username, string $password): bool
    {
        $accountsWithUsername = $this->mySql->preparedStatement()
            ->statement('SELECT `' . $this->hashCol . '` FROM `' . $this->table . '` WHERE `' . $this->usernameCol . '` = ?')
            ->parameters(array('s', $username))
            ->getResults()
        ;
        if (sizeof($accountsWithUsername) > 1) throw new Exception('Multiple accounts exist with the same username: ' . $username);
        if (sizeof($accountsWithUsername) === 0) return FALSE;
        return password_verify($password, $accountsWithUsername[0][$this->hashCol]);
    }

    /**
     * Retrieve the account id associated with a username.
     *
     * @param string $username
     * @return integer|FALSE
     */
    public function getId(string $username): int|FALSE
    {
        $accountsWithUsername = $this->mySql->preparedStatement()
            ->statement('SELECT `' . $this->idCol . '` FROM `' . $this->table . '` WHERE `' . $this->usernameCol . '` = ?')
            ->parameters(array('s', $username))
            ->getResults()
        ;
        if (sizeof($accountsWithUsername) !== 1) return FALSE;
        return $accountsWithUsername[0][$this->idCol];
    }

    /**
     * Retrieve the username associated with an account id.
     *
     * @param string $id
     * @return integer|FALSE
     */
    public function getUsername(string $id): int|FALSE
    {
        $accountsWithId = $this->mySql->preparedStatement()
            ->statement('SELECT `' . $this->usernameCol . '` FROM `' . $this->table . '` WHERE `' . $this->idCol . '` = ?')
            ->parameters(array('s', $id))
            ->getResults()
        ;
        if (sizeof($accountsWithId) !== 1) return FALSE;
        return $accountsWithId[0][$this->idCol];
    }

    /**
     * Set the time and ip for the last time an account was active.
     *
     * @param string $id
     * @param integer $time
     * @param string|null $ip (optional)
     * @return boolean
     */
    public function setSeen(string $id, int $time, ?string $ip = NULL): bool
    {
        $values = ($ip !== NULL) ? [$this->seenCol => $time, $this->ipCol => $ip] : [$this->seenCol => $time];
        $affectedRows = $this->mySql->update()
            ->table($this->table)
            ->addMatch($this->idCol, 'isEqual', $id)
            ->values($values)
            ->getResults()
        ;
        if ($affectedRows !== 1) return FALSE;
        return TRUE;
    }
}
