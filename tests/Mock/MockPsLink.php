<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\PrestaShop\Tests\Mock\Concern\HasStaticFunctionMocks;

abstract class MockPsLink
{
    use HasStaticFunctionMocks;

    public function getAdminBaseLink(): string
    {
        return 'https://example.com/admin';
    }

    public function getAdminLink(): string
    {
        return 'https://example.com/admin/index.php';
    }
}
