<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL;

final class MockMyParcelNL extends MyParcelNL
{
    /**
     * @var string|null
     */
    private $version;

    /**
     * @param  null|string $version
     *
     * @throws \Throwable
     */
    public function __construct(?string $version = null)
    {
        $this->version = $version;
        parent::__construct();
    }

    protected function getVersionFromComposer(): string
    {
        return $this->version ?? parent::getVersionFromComposer();
    }
}
