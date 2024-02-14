<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Api\Adapter;

use GuzzleHttp\Client;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Api\Response\ClientResponse;

class Guzzle5ClientAdapter implements ClientAdapterInterface
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
        $clientRequest = $this->client->createRequest($httpMethod, $uri, $options);

        $clientResponse = $this->client->send($clientRequest);
        $statusCode = $clientResponse->getStatusCode() ?? 0;

        return new ClientResponse($clientResponse->getBody()->getContents(), $statusCode, $clientResponse->getHeaders());
    }
}
