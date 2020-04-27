<?php

namespace Gett\MyParcel\Module\Hooks;

trait LegacyOrderPageHooks
{
    public function hookDisplayAdminListBefore()
    {
        if ($this->context->controller instanceof \AdminOrdersController) {
            \Media::addJsDef([
                //TODO
            ]);
            $this->context->controller->addJS(
                $this->_path . 'resources/js/admin/order.js'
            );

            $link = new \Link();
            $this->context->smarty->assign([
                'action' => $link->getAdminLink('AdminLabel', true, ['action' => 'createLabel']),
                'download_action' => $link->getAdminLink('AdminLabel', true, ['action' => 'downloadLabel']),
            ]);

            return $this->display($this->name, 'views/templates/admin/hook/orders_popups.tpl');
        }
    }

    public function hookActionAdminOrdersListingFieldsModifier($params)
    {
        $params['select'] .= ',1 as `myparcel_void_1` ,1 as `myparcel_void_2`';

        $params['fields']['myparcel_void_1'] = [
            'title' => 'Labels',
            'class' => 'text-nowrap',
            'callback' => 'printMyParcelLabel',
            'search' => false,
            'orderby' => false,
            'remove_onclick' => true,
            'callback_object' => \Module::getInstanceByName($this->name),
        ];

        $params['fields']['myparcel_void_2'] = [
            'title' => '',
            'class' => 'text-nowrap',
            'callback' => 'printMyParcelIcon',
            'search' => false,
            'orderby' => false,
            'remove_onclick' => true,
            'callback_object' => \Module::getInstanceByName($this->name),
        ];
    }

    public function printMyParcelLabel($id, $params)
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('myparcel_order_label');
        $sql->where('id_order = "' . pSQL($params['id_order']) . '" ');
        $result = \Db::getInstance()->executeS($sql);
        $link = new \Link();
        $this->context->smarty->assign([
            'labels' => $result,
            'link' => $link,
        ]);

        return $this->display($this->name, 'views/templates/admin/icon-labels.tpl');
    }

    public function printMyParcelIcon($id, $params)
    {
        return $this->display($this->name, 'views/templates/admin/icon-concept.tpl');
    }

    public function hookActionAdminControllerSetMedia()
    {
        if ($this->context->controller instanceof \AdminOrdersController) {

            $link = new \Link();
            \Media::addJsDef(
                [
                    'default_label_size' => \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_SIZE_CONFIGURATION_NAME) == false ? 'a4' : \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_SIZE_CONFIGURATION_NAME),
                    'default_label_position' => \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_POSITION_CONFIGURATION_NAME) == false ? '1' : \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_POSITION_CONFIGURATION_NAME),
                    'prompt_for_label_position' => \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_PROMPT_POSITION_CONFIGURATION_NAME) == false ? '0' : \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_PROMPT_POSITION_CONFIGURATION_NAME),
                    'create_labels_bulk_route' => $link->getAdminLink('AdminLabel', true, ['action' => 'createLabelsBulk']),
                ]
            );

            $this->context->controller->addJS(
                $this->_path . 'views/js/admin/order.js'
            );
        } elseif ($this->context->controller->php_self == "AdminOrders"){ //symfony controller
            \Media::addJsDef(
                [
                    'default_label_size' => \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_SIZE_CONFIGURATION_NAME) == false ? 'a4' : \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_SIZE_CONFIGURATION_NAME),
                    'default_label_position' => \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_POSITION_CONFIGURATION_NAME) == false ? '1' : \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_POSITION_CONFIGURATION_NAME),
                    'prompt_for_label_position' => \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_PROMPT_POSITION_CONFIGURATION_NAME) == false ? '0' : \Configuration::get(\Gett\MyParcel\Constant::MY_PARCEL_LABEL_PROMPT_POSITION_CONFIGURATION_NAME),
                ]
            );

            $this->context->controller->addJS(
                $this->_path . 'views/js/admin/symfony/orders-list.js'
            );
        }
    }
}