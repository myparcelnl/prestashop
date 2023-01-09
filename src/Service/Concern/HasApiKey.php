<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service\Concern;

/**
 * @deprecated
 */
trait HasApiKey
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param  string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }
}
