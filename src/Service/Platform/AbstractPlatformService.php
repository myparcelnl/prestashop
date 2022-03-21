<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service\Platform;

use Exception;
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
     * If getDefaultCarrier is not overridden, the first entry in this list is considered the default carrier.
     *
     * @return class-string[]
     * @see \Gett\MyparcelBE\Service\Platform\AbstractPlatformService::getDefaultCarrier()
     */
    abstract public function getCarriers(): array;

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
     * @return \MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier
     * @throws \Exception
     */
    public function getDefaultCarrier(): AbstractCarrier
    {
        $carriers = $this->getCarriers();

        if (empty($carriers)) {
            throw new Exception('No carriers set for ' . static::class);
        }

        $carrier = $carriers[0];
        return new $carrier();
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
