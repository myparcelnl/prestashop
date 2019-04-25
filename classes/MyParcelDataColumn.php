<?php
/**
 * 2017-2018 DM Productions B.V.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@dmp.nl so we can send you a copy immediately.
 *
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2018 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    return;
}
if (version_compare(_PS_VERSION_, '1.7.5.0', '<')) {
    return;
}

use Symfony\Component\OptionsResolver\OptionsResolver;

final class MyParcelDataColumn extends PrestaShop\PrestaShop\Core\Grid\Column\AbstractColumn
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'myparcel_data';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver
            ->setRequired(array(
                'field',
            ))
            ->setAllowedTypes('field', 'string')
        ;
    }
}
