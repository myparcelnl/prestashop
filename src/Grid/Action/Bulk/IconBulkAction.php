<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Grid\Action\Bulk;

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
        ]);

        $resolver
            ->setRequired(['material_icon'])
            ->setAllowedTypes('material_icon', 'string');
    }
}
