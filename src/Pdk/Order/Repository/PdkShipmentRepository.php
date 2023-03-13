<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;

class PdkShipmentRepository extends AbstractPdkRepository
{
    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository
     */
    private $psOrderShipmentRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface           $storage
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository $psOrderShipmentRepository
     */
    public function __construct(StorageInterface $storage, PsOrderShipmentRepository $psOrderShipmentRepository)
    {
        parent::__construct($storage);

        $this->psOrderShipmentRepository = $psOrderShipmentRepository;
    }

    /**
     * @param  mixed $input
     *
     * @return void
     */
    public function delete($input): void
    {
        //        $this->psOrderShipmentRepository->($input);
    }

    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     */
    public function get($input): Model
    {
        return $this->retrieve((string) $input, function () use ($input) {
            return $this->psOrderShipmentRepository->find($input);
        });
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $model
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     * @throws \Doctrine\ORM\ORMException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function update(Model $model): Model
    {
        $this->psOrderShipmentRepository->updateOrCreate(
            [
                'idOrder'    => $model->orderId,
                'idShipment' => $model->id,
            ],
            [
                'data' => json_encode($model->toStorableArray()),
            ]
        );

        return $model;
    }
}
