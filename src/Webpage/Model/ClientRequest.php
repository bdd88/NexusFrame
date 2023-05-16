<?php
namespace NexusFrame\Webpage\Model;

/** Stores data related to the client request and server environment. This makes unit testing easier by avoiding the direct usage of global variables. */
class ClientRequest
{
    public object $get;
    public object $post;
    public object $server;

    public function __construct()
    {
        $this->get = (object) $_GET;
        $this->post = (object) $_POST;
        $this->server = (object) $_SERVER;
    }
}
