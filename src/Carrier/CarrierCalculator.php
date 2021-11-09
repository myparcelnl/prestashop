<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Carrier;

use Exception;
use Gett\MyparcelBE\Service\CarrierService;
use MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierFactory;
use PrestaShop\PrestaShop\Adapter\Entity\Carrier;

class CarrierCalculator
{
    public const SOURCE_MYPARCEL   = 'myparcel';
    public const SOURCE_PRESTASHOP = 'prestashop';

    /**
     * @var \MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier
     */
    private $myParcelCarrier;

    /**
     * @var \PrestaShop\PrestaShop\Adapter\Entity\Carrier
     */
    private $prestaShopCarrier;

    /**
     * @param  mixed $data
     *
     * @throws \Exception
     */
    public function __construct($data, ?string $source = null)
    {
        if (! $data) {
            throw new Exception('No data given!');
        }

        $this->findCarrier($data, $source);
    }

    /**
     * @return \MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier
     */
    public function getMyParcelCarrier(): AbstractCarrier
    {
        return $this->myParcelCarrier;
    }

    /**
     * @return \PrestaShop\PrestaShop\Adapter\Entity\Carrier
     */
    public function getPrestaShopCarrier(): Carrier
    {
        return $this->prestaShopCarrier;
    }

    /**
     * @param              $data
     * @param  null|string $source
     *
     * @return void
     * @throws \Exception
     */
    private function findCarrier($data, ?string $source): void
    {
        if (! $data) {
            return;
        }

        switch ($source) {
            case self::SOURCE_PRESTASHOP:
                $this->getFromPrestaShop($data);
                break;
            case self::SOURCE_MYPARCEL:
                $this->getFromMyParcel($data);
                break;
            default:
                $this->getAny($data);
                break;
        }
    }

    /**
     * @throws \Exception
     */
    private function getAny($data): void
    {
        try {
            $this->getFromPrestaShop($data);
        } catch (Exception $e) {
            $this->getFromMyParcel($data);
        }
    }

    /**
     * @param $data
     *
     * @return void
     * @throws \Exception
     */
    private function getFromMyParcel($data): void
    {
        $this->myParcelCarrier   = CarrierFactory::create($data);
        $this->prestaShopCarrier = new Carrier(CarrierService::getPrestaShopCarrierId($this->myParcelCarrier));
    }

    /**
     * @param $data
     *
     * @return void
     * @throws \Exception
     */
    private function getFromPrestaShop($data): void
    {
        $this->prestaShopCarrier = new Carrier($data);
        $this->myParcelCarrier   = CarrierService::getMyParcelCarrier($data);
    }
}
