<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Grid\Column;

use MyParcelNL\Pdk\Facade\Pdk;
use PrestaShop\PrestaShop\Core\Grid\Column\AbstractColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MyParcelOrderColumn extends AbstractColumn
{
    public function __construct()
    {
        $appInfo = Pdk::getAppInfo();

        parent::__construct($appInfo->name);

        $this->setName($appInfo->title);
    }

    /**
     * Corresponds to the filename of the template that will be used for rendering the column.
     *
     * @return string
     * @see /views/PrestaShop/Admin/Common/Grid/Columns/Content/order_box.html.twig
     */
    public function getType(): string
    {
        return 'order_box';
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
