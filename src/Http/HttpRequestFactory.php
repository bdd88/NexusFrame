<?php
namespace NexusFrame\Http;

/** Factory for HttpRequest objects. */
class HttpRequestFactory
{
    private HttpResponseFactory $httpResponseFactory;

    public function __construct(HttpResponseFactory $httpResponseFactory)
    {
        $this->httpResponseFactory = $httpResponseFactory;
    }

    /** Create a HttpRequest object. */
    public function create(string $response, array $info): HttpRequest
    {
        return new HttpRequest($this->httpResponseFactory);
    }
}
