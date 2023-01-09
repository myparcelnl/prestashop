<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Grid\Action\Bulk;

use MyParcelNL\Pdk\Plugin\Service\RenderService;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\AbstractBulkAction;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IconBulkAction extends AbstractBulkAction
{
    public function getType(): string
    {
        return 'icon_button';
    }

    /**
     * @param  \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return void
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'submit_route' => '',
            'event'        => RenderService::EVENT_ACTION,
        ]);

        $resolver
            ->setRequired(['icon'])
            ->setAllowedTypes('icon', 'string');
    }
}
