<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service\Concern;

use Configuration;
use Gett\MyparcelBE\Constant;

trait HasApiKey
{
    /**
     * @var null|string
     */
    private $apiKey;

    /**
     * HasApiKey constructor, provided for backwards compatibility.
     *
     * @param string|null $apiKey
     * @deprecated
     */
    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return null|string
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey ?? $this->fetchApiKey();
    }

    /**
     * @return bool whether this has an api key
     */
    public function hasApiKey(): bool
    {
        return (bool) $this->getApiKey();
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function ensureHasApiKey(): string
    {
        if (! $this->getApiKey()) {
            throw new \RuntimeException('No API key found!');
        }

        return $this->getApiKey();
    }

    /**
     * @return string|null
     */
    private function fetchApiKey(): ?string
    {
        $this->apiKey = Configuration::get(Constant::API_KEY_CONFIGURATION_NAME);

        return $this->apiKey ?: null;
    }
}
