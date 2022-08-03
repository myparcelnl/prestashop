<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Pdk\Api\Adapter;

use GuzzleHttp\Client;
use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Response\ClientResponse;
use MyParcelNL\Pdk\Api\Response\ClientResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class Guzzle5ClientAdapter implements ClientAdapterInterface
{
    private const DEFAULT_OPTIONS = [
        'exceptions' => false,
    ];

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
     * @return \MyParcelNL\Pdk\Api\Response\ClientResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function doRequest(string $httpMethod, string $uri, array $options = []): ClientResponseInterface
    {
        $clientRequest = $this->client->createRequest(
            $httpMethod,
            $uri,
            self::DEFAULT_OPTIONS + $options
        );

        $response   = $this->client->send($clientRequest);
        $statusCode = $response ? $response->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        $body = $response
            ? $response
                ->getBody()
                ->getContents()
            : null;

        return new ClientResponse($body, $statusCode);
    }
}
