<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Grid\Column;

use PrestaShop\PrestaShop\Core\Grid\Column\AbstractColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LabelsColumn extends AbstractColumn
{
    /**
     * @return string
     * @see /views/PrestaShop/Admin/Common/Grid/Columns/Content/labels.html.twig
     */
    public function getType(): string
    {
        return 'labels';
    }

    /**
     * @param  \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return void
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'sortable'  => false,
                'clickable' => false,
            ]);
    }
}
