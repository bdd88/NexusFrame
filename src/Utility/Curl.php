<?php
namespace NexusFrame\Utility;

/** Use curl to perform http requests. */
class Curl
{
    public function __construct(private Logger $logger)
    {
        $this->logger->createLog('curl');
    }

    /** Query a url using http GET or POST and return am array containing the http response code and API response. */
    public function exec(string $method, array $header, string $url, ?string $vars = NULL): array
    {
        $logMessage = $method . ' "' . $url . '"';
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_HTTPHEADER, $header);
        if ($method === 'post') {
            curl_setopt($request, CURLOPT_POST, 1);
            curl_setopt($request, CURLOPT_POSTFIELDS, $vars);
            $logMessage .= ' "' . $vars . '"';
        }
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($request);
        $responseCode = curl_getinfo($request, CURLINFO_RESPONSE_CODE);
        curl_close($request);
        $this->logger->log('curl', $logMessage);
        return ['code' => $responseCode, 'response' => $response];
    }
    
}
