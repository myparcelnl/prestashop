<?php

namespace MyParcelNL\PrestaShop\Service;

class ErrorMessage
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function get(string $message = ''): string
    {
        if (empty($message)) {
            return '';
        }

        $parsedMessage = '';
        switch (true) {
            case strpos($message, 'cc not supported') !== false:
                $parsedMessage = $this->module->l(
                    'Shipment validation error: Country not supported.',
                    'errormessage'
                );
                break;
            case 0 === strpos($message, 'Invoice id is required for international shipments.'):
                $parsedMessage = $message;
                break;
        }

        return $parsedMessage;
    }
}
