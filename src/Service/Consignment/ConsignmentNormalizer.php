<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service\Consignment;

use MyParcelNL\PrestaShop\Module\Facade\ModuleService;
use MyParcelNL;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\BpostConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;

class ConsignmentNormalizer
{
    /**
     * @var array|null
     */
    private $data;

    /**
     * @param array|null $data
     */
    public function __construct(?array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function normalize(): array
    {
        $data                 = $this->data;
        new MyParcelNL();
        $data['carrier']      = $data['carrier'] ?? (ModuleService::isBE() ? BpostConsignment::CARRIER_NAME : PostNLConsignment::CARRIER_NAME) ;
        $data['deliveryType'] = $data['deliveryType'] ?? AbstractConsignment::DELIVERY_TYPE_STANDARD_NAME;
        $data['package_type'] = $data['package_type'] ?? AbstractConsignment::PACKAGE_TYPE_PACKAGE;

        return $data;
    }
}
