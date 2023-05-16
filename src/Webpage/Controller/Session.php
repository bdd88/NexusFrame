<?php
namespace NexusFrame\Webpage\Controller;

use NexusFrame\Utility\Logger;
use NexusFrame\Database\MySql\MySql;

/** Handles user sessions. */
class Session
{
    private MySql $mySql;
    private Logger $logger;
    private int $accountId;

    public function __construct(MySql $mySql, Logger $logger)
    {
        $this->mySql = $mySql;
        $this->logger = $logger;
        $this->start();
    }

    /** Return the account ID of the current session. */
    public function getAccountId(): int
    {
        return $this->accountId;
    }

    /** Attempt to authenticate the user. */
    public function authenticate(string $username, string $password, string $ip): bool
    {
        $userId = $this->mySql->verifyLogin($username, $password);  // TODO: Created prepared statement for authentication against the database.
        if ($userId === FALSE) {
            $this->logger->log('login', "IP: $ip User: $username - Failed Login.");
            return FALSE;
        } else {
            $this->update($userId);
            $this->logger->log('login', "IP: $ip User: $username - Successful Login.");
            return TRUE;
        }
    }

    /** Create session and store data in a cookie. */
    private function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $cookieParams = session_get_cookie_params();
            $cookieParams['secure'] = 'true';
            $cookieParams['HttpOnly'] = 'true';
            $cookieParams['SameSite'] = 'Strict';
            session_set_cookie_params($cookieParams);
            session_start();
        }
    }

    /** Update session data. */
    private function update(int $id): void
    {
        $this->accountId = $id;
        $_SESSION['accountId'] = $this->accountId;
        $_SESSION['expire'] = time() + $this->appSettings['login_timeout']; // TODO: Pull settings from config, database, or require as a parameter.
        $this->mySql->update('accounts', [
            ['account_id', 'isEqual', $this->accountId]
        ], ['lastSeen' => time()]);
        session_regenerate_id();
    }

    /** Check the current session, and update it based on session expiration. */
    public function status(): bool
    {
        if (!isset($_SESSION['accountId'])) return FALSE;
        if (time() >= $_SESSION['expire']) {
            $this->restart();
            return FALSE;
        }
        $this->update($_SESSION['accountId']);
        return TRUE;
    }

    /** Destroy the current session. */
    public function stop(): void
    {
        unset($this->accountId);
        session_unset();
        session_destroy();
    }

    /** Recreate the session. */
    public function restart(): void
    {
        $this->stop();
        $this->start();
    }

}
