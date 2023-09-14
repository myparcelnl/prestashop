<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use Throwable;

/**
 * @property \Context $context
 */
trait HasPsCarrierHooks
{
    /**
     * Prevents carrier id from de-synchronising with our mappings when user modifies it.
     *
     * @see https://devdocs.prestashop-project.org/8/modules/carrier/
     *
     * @param  array $params
     *
     * @return void
     */
    public function hookActionCarrierUpdate(array $params): void
    {
        $oldId = (int) $params['id_carrier'];
        $newId = (int) $params['carrier']->id;

        try {
            $this->updateCarrierId($oldId, $newId);
        } catch (Throwable $e) {
            Logger::error(
                'Failed to update carrier id',
                [
                    'exception' => $e,
                    'oldId'     => $oldId,
                    'newId'     => $newId,
                ]
            );
        }
    }

    /**
     * @param  int $oldId
     * @param  int $newId
     *
     * @return void
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateCarrierId(int $oldId, int $newId): void
    {
        /** @var PsCarrierMappingRepository $carrierMappingRepository */
        $carrierMappingRepository = Pdk::get(PsCarrierMappingRepository::class);

        $carrierMappingRepository->update(['carrierId' => $newId], ['carrierId' => $oldId]);

        EntityManager::flush();
    }
}
