<?php

/**
 * This model represents one request
 *
 * If you want to add improvements, please create a fork in our GitHub:
 * https://github.com/myparcelnl
 *
 * @author      Reindert Vetter <reindert@myparcel.nl>
 * @copyright   2010-2017 MyParcel
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US  CC BY-NC-ND 3.0 NL
 * @link        https://github.com/myparcelnl/sdk
 * @since       File available since Release v0.1.0
 */
namespace MyParcelModule\MyParcelNL\Sdk\src\Model;

use MyParcelModule\Curl\Curl;
use InvalidArgumentException;
use MyParcelModule\MyParcelNL\Sdk\src\Exception\ApiException;
use MyParcelModule\MyParcelNL\Sdk\src\Exception\ConnectionException;
class MyParcelRequest
{
    /**
     * API URL
     */
    const REQUEST_URL = 'https://api.myparcel.nl';
    /**
     * Supported request types.
     */
    const REQUEST_TYPE_SHIPMENTS = 'shipments';
    const REQUEST_TYPE_RETRIEVE_LABEL = 'shipment_labels';
    /**
     * API headers
     */
    const REQUEST_HEADER_SHIPMENT = 'Content-Type: application/vnd.shipment+json; charset=utf-8';
    const REQUEST_HEADER_RETRIEVE_SHIPMENT = 'Accept: application/json; charset=utf8';
    const REQUEST_HEADER_RETRIEVE_LABEL_LINK = 'Accept: application/json; charset=utf8';
    const REQUEST_HEADER_RETRIEVE_LABEL_PDF = 'Accept: application/pdf';
    const REQUEST_HEADER_RETURN = 'Content-Type: application/vnd.return_shipment+json; charset=utf-8';
    const REQUEST_HEADER_DELETE = 'Accept: application/json; charset=utf8';
    /**
     * @var string
     */
    private $api_key = '';
    private $headers = array();
    private $body = '';
    private $error = null;
    private $result = null;
    private $userAgent = null;
    private static $clientClass = '\\MyParcelModule\\MyParcelNL\\Sdk\\src\\Helper\\MyParcelCurl';
    public static function setHttpClientClass($client)
    {
        if (!is_subclass_of($client, '\\MyParcelModule\\Curl\\Curl')) {
            throw new \InvalidArgumentException('Invalid HTTP client given. It should extend \\Curl\\Curl');
        }
        static::$clientClass = $client;
    }
    public function getHttpClientInstance()
    {
        return new static::$clientClass();
    }
    /**
     * @return null
     */
    public function getResult()
    {
        return $this->result;
    }
    /**
     * Sets the parameters for an API call based on a string with all required request parameters and the requested API
     * method.
     *
     * @param string $apiKey
     * @param string $body
     * @param string $requestHeader
     *
     * @return $this
     */
    public function setRequestParameters($apiKey, $body = '', $requestHeader = '')
    {
        $this->api_key = $apiKey;
        $this->body = $body;
        $headers = array();
        $header = explode(':', $requestHeader);
        $headers[trim($header[0])] = trim($header[1]);
        $headers['Authorization'] = 'basic ' . base64_encode($this->api_key);
        $this->headers = $headers;
        return $this;
    }
    /**
     * send the created request to MyParcel
     *
     * @param string $method
     *
     * @param string $uri
     *
     * @return MyParcelRequest|array|false|string
     *
     * @throws ApiException
     * @throws ConnectionException
     * @throws \ErrorException
     */
    public function sendRequest($method = 'POST', $uri = self::REQUEST_TYPE_SHIPMENTS)
    {
        if (!$this->checkConfigForRequest()) {
            return false;
        }
        //instantiate the http client
        /** @var Curl $request */
        $request = $this->getHttpClientInstance();
        $request->setHeaders($this->headers);
        $request->setUserAgent($this->getUserAgent());
        $request->setDefaultJsonDecoder(true);
        $url = $this->getRequestUrl($uri);
        // Perform the curl request
        if ($method == 'POST') {
            $request->post($url, $this->body);
        } elseif ($method == 'DELETE') {
            if ($this->body) {
                $url .= '/' . $this->body;
            }
            $request->delete($url);
        } else {
            if ($this->body) {
                $url .= '/' . $this->body;
            }
            $request->get($url);
        }
        // Read the response
        $response = $request->getResponse();
        if (is_string($response) && preg_match('/^%PDF-1./', $response)) {
            $this->result = $response;
        } else {
            $this->result = $response;
            if ($response === false) {
                throw new \MyParcelModule\MyParcelNL\Sdk\src\Exception\ConnectionException($request->curlErrorMessage ?: $request->errorMessage, $request->curlErrorCode);
            }
            $this->checkMyParcelErrors();
        }
        $request->close();
        if ($this->getError()) {
            throw new \MyParcelModule\MyParcelNL\Sdk\src\Exception\ApiException("Error in MyParcel API request: {$this->getError()} Url: {$url} Request: {$this->body}");
        }
        return $this;
    }
    /**
     * Checks if all the requirements are set to send a request to MyParcel
     *
     * @return bool
     *
     * @throws ApiException
     */
    private function checkConfigForRequest()
    {
        if (empty($this->api_key)) {
            throw new \MyParcelModule\MyParcelNL\Sdk\src\Exception\ApiException('api_key not found');
        }
        return true;
    }
    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }
    /**
     * @param string $userAgent
     *
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }
    /**
     * Get version of in composer file
     */
    public function getUserAgentFromComposer()
    {
        return 'MyParcelNL-SDK/1.5.2';
    }
    /**
     * Get request url
     *
     * @param string $uri
     *
     * @return string
     */
    private function getRequestUrl($uri)
    {
        $url = self::REQUEST_URL . '/' . $uri;
        return $url;
    }
    /**
     * Check if MyParcel gives an error
     *
     * @return void
     */
    private function checkMyParcelErrors()
    {
        if (!is_array($this->result)) {
            return;
        }
        if (empty($this->result['errors'])) {
            return;
        }
        foreach ($this->result['errors'] as $error) {
            if ((int) key($error) > 0) {
                $error = current($error);
            }
            $errorMessage = '';
            if (key_exists('message', $this->result)) {
                $message = $this->result['message'];
            } elseif (key_exists('message', $error)) {
                $message = $error['message'];
            } else {
                $message = 'Unknown error: ' . json_encode($error) . '. Please contact MyParcel.';
            }
            if (key_exists('code', $error)) {
                $errorMessage = $error['code'];
            } elseif (key_exists('fields', $error)) {
                $errorMessage = $error['fields'][0];
            }
            $humanMessage = key_exists('human', $error) ? $error['human'][0] : '';
            $this->error = $errorMessage . ' - ' . $humanMessage . ' - ' . $message;
            break;
        }
    }
    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
