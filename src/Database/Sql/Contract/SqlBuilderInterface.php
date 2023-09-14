<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database\Sql\Contract;

interface SqlBuilderInterface
{
    /**
     * @param  string $table
     */
    public function __construct(string $table);

    public function build(): string;
}
