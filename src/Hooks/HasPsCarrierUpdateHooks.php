<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use Throwable;

/**
 * @property \Context $context
 */
trait HasPsCarrierUpdateHooks
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
            $this->moveCarrierImage($oldId, $newId);
        } catch (Throwable $e) {
            Logger::error(
                'Failed to update carrier',
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
     */
    private function moveCarrierImage(int $oldId, int $newId): void
    {
        /** @var \MyParcelNL\Pdk\Base\FileSystemInterface $fileSystem */
        $fileSystem = Pdk::get(FileSystemInterface::class);

        foreach (Pdk::get('carrierLogoFileExtensions') as $fileExtension) {
            $oldLogoFile = _PS_SHIP_IMG_DIR_ . $oldId . $fileExtension;

            if (! $fileSystem->fileExists($oldLogoFile)) {
                return;
            }

            $newLogoFile = _PS_SHIP_IMG_DIR_ . $newId . $fileExtension;

            $fileSystem->put($newLogoFile, $fileSystem->get($oldLogoFile));
            $fileSystem->unlink($oldLogoFile);
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
    private function updateCarrierId(int $oldId, int $newId): void
    {
        /** @var PsCarrierMappingRepository $carrierMappingRepository */
        $carrierMappingRepository = Pdk::get(PsCarrierMappingRepository::class);

        $carrierMappingRepository->update(
            [MyparcelnlCarrierMapping::CARRIER_ID => $newId],
            [MyparcelnlCarrierMapping::CARRIER_ID => $oldId]
        );

        EntityManager::flush();
    }
}
