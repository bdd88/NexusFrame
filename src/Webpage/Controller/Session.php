<?php
namespace NexusFrame\Webpage\Controller;

use NexusFrame\Utility\Logger;

/** Handles user sessions. */
class Session
{
    public function __construct(
        private AccountManager $accountManager,
        private Logger $logger,
        private ?int $loginTimeout = NULL)
    {
        $this->loginTimeout ??= 3600;
        $this->start();
    }

    /** Attempt to authenticate the user. */
    public function authenticate(string $username, string $password): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($this->accountManager->authenticate($username, $password) === FALSE) {
            $this->logger->log('login', "IP: $ip User: $username - Failed Login.");
            return FALSE;
        }
        session_regenerate_id();
        $_SESSION['accountId'] = $this->accountManager->getId($username);
        $_SESSION['username'] = $this->accountManager->getUsername($_SESSION['accountId']);
        $this->update();
        $this->logger->log('login', "IP: $ip User: $username - Successful Login.");
        return TRUE;
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

    /** Refresh the logout expiration, set the last seen time, and regenerate session id. */
    private function update(): void
    {
        $currentTime = time();
        $_SESSION['expire'] = $currentTime + $this->loginTimeout;
        $this->accountManager->setSeen($_SESSION['accountId'], $currentTime, $_SERVER['REMOTE_ADDR']);
    }

    /**
     * Check the current session to see if a user is logged in, and has not exceeded the login timeout.
     * Refreshes the login timeout and calls update() to refresh the last seen data.
     *
     * @return boolean TRUE if a user is logged in and the session is not expired, FALSE otherwise.
     */
    public function status(): bool
    {
        if (!isset($_SESSION['accountId']) || time() >= $_SESSION['expire']) {
            $this->restart();
            return FALSE;
        }
        $this->update();
        return TRUE;
    }

    /** Destroy the current session. */
    public function stop(): void
    {
        session_unset();
        session_destroy();
    }

    /** Recreate the session. */
    public function restart(): void
    {
        $this->stop();
        $this->start();
    }

    public function setLoginTimeout(int $seconds): void
    {
        $this->loginTimeout = $seconds;
    }

}
