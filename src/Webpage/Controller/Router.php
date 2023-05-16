<?php
namespace NexusFrame\Webpage\Controller;

class Router
{
    //private Session $session;

    /** Store the routes from the config file. */
    public function __construct()
    {
        //$this->session = $session;
    }

    public function route(array $configuredPages, string $requestedPage): string|FALSE
    {
        if (!isset($configuredPages[$requestedPage])) {
            $errorCode = 404;
        } elseif ($configuredPages[$requestedPage]['enabled'] === FALSE) {
            $errorCode = 403;
        //} elseif ($configuredPages[$requestedPage]['loginRequired'] === TRUE && $this->session->status() === FALSE) { // TODO: Implement session checking once session class is fixed.
        } elseif ($configuredPages[$requestedPage]['login'] === TRUE) {
            $errorCode = 401;
        }
        if (isset($errorCode)) {
            http_response_code($errorCode);
            if (isset($configuredPages[$errorCode])) return $errorCode;
            return FALSE;
        }
        return $requestedPage;
    }
}
