<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Script\Contract;

interface PsScriptServiceInterface
{
    /**
     * @param  \AdminController|\FrontController $controller
     * @param  string                            $path
     *
     * @return void
     */
    public function register($controller, string $path): void;
}
