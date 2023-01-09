<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Service;

class RenderService extends \MyParcelNL\Pdk\Plugin\Service\RenderService
{
    /**
     * @return string
     * @throws \Exception
     */
    public function getInitHtml(): string
    {
        $module = \MyParcelNL::getModule();
        $twig   = $module->get('twig');

        if (! $twig) {
            throw new \RuntimeException('Twig not found');
        }

        return $twig->render(
            "{$module->getLocalPath()}views/templates/admin/pdk/init.twig",
            [
                'path' => $module->getPathUri(),
            ]
        );
    }
}
