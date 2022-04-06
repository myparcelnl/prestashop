<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks\Helpers;

use Gett\MyparcelBE\Module\Hooks\AdminPanelRenderService;
use Gett\MyparcelBE\Service\Concern\HasInstance;
use Gett\MyparcelBE\Service\ControllerService;
use Media;
use MyParcelNL\Sdk\src\Support\Arr;

class AdminOrderList extends AbstractAdminOrder
{
    use HasInstance;

    /**
     * @return string
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function getAdminAfterHeader(): string
    {
        return (new AdminPanelRenderService())->renderModals();
    }

    /**
     * @param  string $key
     *
     * @return null|string
     */
    public static function getTranslation(string $key): ?string
    {
        $instance = self::getInstance();
        return Arr::get($instance->getTranslations(), $key);
    }

    /**
     * @throws \Exception
     */
    public function setHeaderContent(): void
    {
        Media::addJsDef([
            'MyParcelActions'       => $this->getActions(),
            'MyParcelConfiguration' => $this->getConfiguration(),
            'MyParcelTranslations'  => $this->getTranslations(),
        ]);

        $this->context->controller->addJqueryPlugin(['scrollTo']);

        $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/myparcel.css');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/dist/js/admin/order.js');
    }

    /**
     * Actions for doing requests from the admin backoffice.
     *
     * @return array
     * @throws \Exception
     */
    private function getActions(): array
    {
        return [
            'adminUrl'           => $this->context->link->getAdminBaseLink(),
            'deliveryOptionsUrl' => $this->context->link->getModuleLink($this->module->name, 'checkout'),
            'pathLabel'          => ControllerService::generateUri(ControllerService::LABEL),
            'pathLoading'        => ControllerService::generateUri(ControllerService::LOADING),
            'pathOrder'          => ControllerService::generateUri(ControllerService::ORDER),
        ];
    }

    /**
     * @return array
     */
    private function getConfiguration(): array
    {
        return [
            'currencySign'   => $this->context->currency->getSign(),
            'dateFormatFull' => $this->context->language->date_format_full,
            'dateFormatLite' => $this->context->language->date_format_lite,
            'modulePathUri'  => $this->module->getPathUri(),
        ];
    }

    /**
     * Translations used in the admin backoffice.
     *
     * @return array
     */
    private function getTranslations(): array
    {
        $l = function (string $string, string $namespace = 'myparcelbe') {
            return $this->module->l($string, $namespace);
        };

        return [
            'action_create_return_label'         => $l('Create return label'),
            'action_delete'                      => $l('Delete', 'AdminActions'),
            'action_export_and_print_labels'     => $l('Export and print labels'),
            'action_export_labels'               => $l('Export labels'),
            'action_new_shipment'                => $l('New shipment'),
            'action_new_shipment_print'          => $l('New shipment & print'),
            'action_print'                       => $l('Print'),
            'action_print_labels'                => $l('Print labels'),
            'action_refresh'                     => $l('Refresh'),
            'action_refresh_labels'              => $l('Refresh labels'),
            'bulk_actions'                       => $l('Bulk actions', 'AdminGlobal'),
            'cancel'                             => $l('Cancel', 'AdminActions'),
            'concept'                            => $l('Concept'),
            'create'                             => $l('Create', 'AdminActions'),
            'custom_label'                       => $l('Custom label'),
            'customer_email'                     => $l('Customer email'),
            'customer_name'                      => $l('Customer name'),
            'delivery_date_changed'              => $l('The delivery timeframe has been moved to a new date.'),
            'delivery_options_title'             => $l('Delivery options'),
            'edit'                               => $l('Edit', 'AdminActions'),
            'error_create_label'                 => $l('Cannot create label for orders'),
            'error_no_order_selected'            => $l('Please select at least one order that has a MyParcel carrier.'),
            'export'                             => $l('Export'),
            'extra_options_digital_stamp_weight' => $l('Digital stamp weight'),
            'extra_options_label_amount'         => $l('Label amount'),
            'format'                             => $l('Format'),
            'format_a4'                          => $l('A4'),
            'format_a6'                          => $l('A6'),
            'no_shipments'                       => $l('There are no shipments.'),
            'none'                               => $l('None', 'AdminGlobal'),
            'order_calculated_weight'            => $l('Calculated weight:'),
            'order_labels_column_actions'        => $l('Actions'),
            'order_labels_column_last_update'    => $l('Last update'),
            'order_labels_column_status'         => $l('Status'),
            'order_labels_column_track_trace'    => $l('Track & Trace'),
            'order_labels_header'                => $l('Shipments'),
            'output'                             => $l('Label output'),
            'output_download'                    => $l('Download'),
            'output_open'                        => $l('Open'),
            'positions'                          => $l('Positions'),
            'positions_bottom_left'              => $l('Bottom left'),
            'positions_bottom_right'             => $l('Bottom right'),
            'positions_top_left'                 => $l('Top left'),
            'positions_top_right'                => $l('Top right'),
            'print'                              => $l('Print'),
            'print_options_title'                => $l('Print options'),
            'return_prefix'                      => $l('Return: '),
            'returns_form_title'                 => $l('Email return label to your customer'),
            'save'                               => $l('Save', 'AdminActions'),
            'shipment_options_age_check'         => $l('Age check'),
            'shipment_options_insurance'         => $l('Insurance'),
            'shipment_options_insurance_amount'  => $l('Insurance amount'),
            'shipment_options_only_recipient'    => $l('Only recipient'),
            'shipment_options_package_format'    => $l('Package format'),
            'shipment_options_return'            => $l('Direct return'),
            'shipment_options_signature'         => $l('Signature'),
            'shipment_options_title'             => $l('Shipment options'),
            'shipment_package_type'              => $l('Package type'),
            'toggle_dropdown'                    => $l('Toggle dropdown'),
            'up_to'                              => $l('Up to'),
        ];
    }
}
