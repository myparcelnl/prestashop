<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Grid\Column;

use MyParcelNL;
use MyParcelNL\Pdk\Facade\Pdk;
use PrestaShop\PrestaShop\Core\Grid\Column\AbstractColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MyParcelOrderColumn extends AbstractColumn
{
    public function __construct()
    {
        parent::__construct(MyParcelNL::MODULE_NAME);

        $this->setName('MyParcel');
    }

    /**
     * Corresponds to the filename of the template that will be used for rendering the column.
     *
     * @return string
     * @see /views/PrestaShop/Admin/Common/Grid/Columns/Content/order_grid_item.html.twig
     */
    public function getType(): string
    {
        return 'order_grid_item';
    }

    /**
     * @param  \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return void
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(Pdk::get('orderColumnOptions'));
    }
}
