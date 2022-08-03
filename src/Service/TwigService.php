<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

class TwigService
{

    /**
     * @var false|object
     */
    private $twig;

    /**
     * @param  \MyParcelNL $module
     *
     * @throws \Exception
     */
    public function __construct(\MyParcelNL $module)
    {
        $this->twig = $module->get('twig');

        if (! $this->twig) {
            throw new \RuntimeException('Twig not found');
        }
    }
}
