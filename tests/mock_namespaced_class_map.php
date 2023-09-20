<?php
/** @noinspection AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Foundation\Database;

/** @see \PrestaShop\PrestaShop\Core\Foundation\Database\EntityInterface */
interface EntityInterface
{
    public static function getRepositoryClassName(): string;

    public function delete(): void;

    public function hydrate(array $keyValueData): void;

    public function save(): void;
}

