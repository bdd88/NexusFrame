<?php
namespace NexusFrame\Http;

use Exception;

/** Performs HTTP queries using CURL. */
class HttpRequest
{
    private HttpResponseFactory $httpResponseFactory;

    public function __construct(HttpResponseFactory $httpResponseFactory)
    {
        $this->httpResponseFactory = $httpResponseFactory;
    }

    public function query(string $url, ?string $method = NULL, ?string $header = NULL, ?string $payload = NULL): HttpResponse
    {
        // Setup the curl query.
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_URL, $url);
        if ( $method !== NULL) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }
        if ($header !== NULL) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        if ($payload !== NULL) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        // Execute the query and store the response.
        $response = curl_exec($curl);
        if ($response === false) {
            throw new Exception(curl_error($curl), curl_errno($curl));
        }
        $info = curl_getinfo($curl);
        curl_close($curl);

        return $this->httpResponseFactory->create($response, $info);
    }

}
