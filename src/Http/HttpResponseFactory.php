<?php
namespace NexusFrame\Http;

/** Factory for HttpResponse objects. */
class HttpResponseFactory
{
    /** Create a HttpResponse object. */
    public function create(string $response, array $info): HttpResponse
    {
        return new HttpResponse($response, $info);
    }
}
