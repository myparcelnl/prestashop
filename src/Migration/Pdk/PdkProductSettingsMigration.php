<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use Generator;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Migration\AbstractPsMigration;
use MyParcelNL\PrestaShop\Migration\Util\DataMigrator;
use MyParcelNL\PrestaShop\Migration\Util\MigratableValue;
use MyParcelNL\PrestaShop\Migration\Util\ToPackageTypeName;
use MyParcelNL\PrestaShop\Migration\Util\ToTriStateValue;
use MyParcelNL\PrestaShop\Migration\Util\TransformValue;
use MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository;
use MyParcelNL\Sdk\Support\Collection;

final class PdkProductSettingsMigration extends AbstractPsPdkMigration
{
    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository
     */
    private $productSettingsRepository;

    /**
     * @var DataMigrator
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
     * @return \Generator<\MyParcelNL\PrestaShop\Migration\Util\MigratableValue>
     */
    private function getProductSettingsTransformationMap(): Generator
    {
        yield new MigratableValue(
            'MYPARCELNL_PACKAGE_TYPE',
            ProductSettings::PACKAGE_TYPE,
            new ToPackageTypeName(TriStateService::INHERIT)
        );

        yield new MigratableValue(
            'MYPARCELNL_AGE_CHECK',
            ProductSettings::EXPORT_AGE_CHECK,
            new ToTriStateValue()
        );

        yield new MigratableValue(
            'MYPARCELNL_RECIPIENT_ONLY',
            ProductSettings::EXPORT_ONLY_RECIPIENT,
            new ToTriStateValue()
        );

        yield new MigratableValue(
            'MYPARCELNL_RETURN_PACKAGE',
            ProductSettings::EXPORT_RETURN,
            new ToTriStateValue()
        );

        yield new MigratableValue(
            'MYPARCELNL_SIGNATURE_REQUIRED',
            ProductSettings::EXPORT_SIGNATURE,
            new ToTriStateValue()
        );

        yield new MigratableValue(
            'MYPARCELNL_INSURANCE',
            ProductSettings::EXPORT_INSURANCE,
            new ToTriStateValue()
        );

        yield new MigratableValue(
            'MYPARCELNL_PACKAGE_FORMAT',
            ProductSettings::EXPORT_LARGE_FORMAT,
            new TransformValue(function ($value) {
                switch ((int) $value) {
                    case 1:
                        return TriStateService::DISABLED;

                    case 2:
                        return TriStateService::ENABLED;

                    default:
                        return TriStateService::INHERIT;
                }
            })
        );

        yield new MigratableValue(
            'MYPARCELNL_CUSTOMS_CODE',
            ProductSettings::CUSTOMS_CODE,
            new ToTriStateValue(TriStateService::TYPE_STRING)
        );

        yield new MigratableValue(
            'MYPARCELNL_CUSTOMS_ORIGIN',
            ProductSettings::COUNTRY_OF_ORIGIN,
            new TransformValue(function ($value) {
                // Assume the user did not intend to set Afghanistan as the country of origin. It's the default value
                // that is saved whenever the user saves any product, even without opening the module settings.
                if (CountryCodes::CC_AF === $value) {
                    return TriStateService::INHERIT;
                }

                return in_array($value, CountryCodes::ALL, true)
                    ? $value
                    : TriStateService::INHERIT;
            })
        );
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     */
    private function migrateProductSettings(): void
    {
        $allRows       = $this->getAllRows(AbstractPsMigration::LEGACY_TABLE_PRODUCT_CONFIGURATION);
        $rowsByProduct = $allRows->groupBy('id_product');

        $rowsByProduct->each(function (Collection $rows, $productId) {
            if ($this->productSettingsRepository->findOneBy(['productId' => $productId])) {
                Logger::info("Product settings for product $productId already exist, skipping");

                return;
            }

            $oldSettings = $rows
                ->pluck('value', 'name')
                ->toArrayWithoutNull();

            $newSettings = $this->valueMigrator->transform($oldSettings, $this->getProductSettingsTransformationMap());

            $this->productSettingsRepository->create([
                'productId' => (int) $productId,
                'data'      => json_encode(['settings' => $newSettings]),
            ]);
        });

        EntityManager::flush();
    }
}
