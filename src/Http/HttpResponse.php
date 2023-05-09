<?php
namespace NexusFrame\Http;

/** Contains data from the response to a CURL query. */
class HttpResponse
{
    private string $response;
    private array $info;

    public function __construct(string $response, array $info)
    {
        $this->response = $response;
        $this->info = $info;
    }

}
