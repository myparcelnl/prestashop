<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service\Platform;

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Service\Concern\HasInstance;
use MyParcelBE;
use MyParcelNL\Sdk\src\Factory\ConsignmentFactory;
use MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierFactory;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use PrestaShop\PrestaShop\Adapter\Entity\Configuration;

abstract class AbstractPlatformService
{
    use HasInstance;

    /**
     * @return \MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier
     */
    abstract public function getDefaultCarrier(): AbstractCarrier;

    /**
     * @param  \MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier|int|string|null $carrier
     *
     * @return \MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment
     * @throws \Exception
     */
    public function generateConsignment($carrier = null): AbstractConsignment
    {
        $carrier = $carrier
            ? CarrierFactory::create($carrier)
            : $this->getDefaultCarrier();

        return ConsignmentFactory::createFromCarrier($carrier)
            ->setApiKey(Configuration::get(Constant::API_KEY_CONFIGURATION_NAME));
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getUserAgents(): array
    {
        return [
            'MyParcelBE-PrestaShop' => MyParcelBE::getModule()->version,
            'PrestaShop'            => _PS_VERSION_,
        ];
    }
}
