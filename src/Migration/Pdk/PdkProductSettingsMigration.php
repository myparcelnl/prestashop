<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use MyParcelNL\PrestaShop\Migration\AbstractLegacyPsMigration;
use MyParcelNL\PrestaShop\Migration\Util\DataMigrator;
use MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository;

final class PdkProductSettingsMigration extends AbstractPsPdkMigration
{
    private const LEGACY_PRODUCT_SETTINGS_MAP = [
        'MYPARCELNL_PACKAGE_TYPE'       => 'packageType',
        'MYPARCELNL_CUSTOMS_ORIGIN'     => 'countryOfOrigin',
        'MYPARCELNL_CUSTOMS_CODE'       => 'customsCode',
        'MYPARCELNL_INSURANCE'          => 'exportInsurance',
        'MYPARCELNL_SIGNATURE_REQUIRED' => 'exportSignature',
        'MYPARCELNL_RETURN_PACKAGE'     => 'exportReturn',
        'MYPARCELNL_PACKAGE_FORMAT'     => 'exportLargeFormat',
        'MYPARCELNL_ONLY_RECIPIENT'     => 'exportOnlyRecipient',
        'MYPARCELNL_AGE_CHECK'          => 'exportAgeCheck',
    ];

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository
     */
    private $productSettingsRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Migration\Util\PsConfigurationDataMigrator
     */
    private $valueMigrator;

    /**
     * @param  \MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository $productSettingsRepository
     * @param  \MyParcelNL\PrestaShop\Migration\Util\DataMigrator            $valueMigrator
     */
    public function __construct(PsProductSettingsRepository $productSettingsRepository, DataMigrator $valueMigrator)
    {
        parent::__construct();
        $this->productSettingsRepository = $productSettingsRepository;
        $this->valueMigrator             = $valueMigrator;
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     */
    public function up(): void
    {
        $this->migrateProductSettings();
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \Doctrine\ORM\ORMException
     */
    private function migrateProductSettings(): void
    {
        $oldProductSettings = $this->getAllRows(AbstractLegacyPsMigration::LEGACY_TABLE_PRODUCT_CONFIGURATION);

        $productsWithSettings = [];

        foreach ($oldProductSettings as $oldProductSetting) {
            if (! array_key_exists($oldProductSetting['name'], self::LEGACY_PRODUCT_SETTINGS_MAP)) {
                continue;
            }

            $productsWithSettings[$oldProductSetting['id_product']][self::LEGACY_PRODUCT_SETTINGS_MAP[$oldProductSetting['name']]] =
                $oldProductSetting['value'];
        }

        foreach ($productsWithSettings as $productId => $productSettings) {
            $this->productSettingsRepository->updateOrCreate(
                [
                    'productId' => (int) $productId,
                ],
                [
                    'data' => json_encode([
                        'id'   => 'product',
                        'data' => $productSettings,
                    ]),
                ]
            );
        }
    }
}
