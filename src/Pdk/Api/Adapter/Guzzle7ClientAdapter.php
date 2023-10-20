<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Api\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Api\Response\ClientResponse;

final class Guzzle7ClientAdapter implements ClientAdapterInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @param  \GuzzleHttp\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function doRequest(string $httpMethod, string $uri, array $options = []): ClientResponseInterface
    {
        $method = strtolower($httpMethod);

        $requestOptions = array_filter([
            RequestOptions::HEADERS => $options['headers'] ?? null,
            RequestOptions::BODY    => $options['body'] ?? null,
        ]);

        $requestOptions[RequestOptions::HTTP_ERRORS] = false;

        $response     = $this->client->request($method, $uri, $requestOptions);
        $responseBody = $response->getBody();

        $body = $responseBody->isReadable()
            ? $responseBody->getContents()
            : null;

        return new ClientResponse($body, $response->getStatusCode());
    }
}
