<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Module;
use MyParcelNL\Pdk\Context\Service\ContextService;
final class PsPdkContextService extends ContextService
{
    /**
     * Check if we're on a MyParcel admin page
     * Only render PDK components on our own pages to avoid conflicts
     */
    public function shouldRenderPdkComponents(): bool
    {
        return Module::getInstanceByName('myparcelnl') instanceof \MyParcelNL;
    }
}
