<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;

final class MockMigration implements MigrationInterface
{
    private $downCalls = 0;

    private $upCalls   = 0;

    public function down(): void
    {
        $this->downCalls++;
    }

    public function getDownCalls(): int
    {
        return $this->downCalls;
    }

    public function getUpCalls(): int
    {
        return $this->upCalls;
    }

    public function getVersion(): string
    {
        return '999.0.0';
    }

    public function up(): void
    {
        $this->upCalls++;
    }
}
