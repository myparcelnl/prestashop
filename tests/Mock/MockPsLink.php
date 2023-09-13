<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

abstract class MockPsLink extends BaseMock
{
    public function getAdminBaseLink(): string
    {
        return 'https://example.com/admin';
    }

    public function getAdminLink(): string
    {
        return 'https://example.com/admin/index.php';
    }
}
