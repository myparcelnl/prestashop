<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Carrier;

use MyParcelNL\PrestaShop\Carrier\PackageTypeCalculator;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Module\Facade\ModuleService;
use MyParcelNL;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierFactory;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;

class CarrierOptionsCalculator
{
    public const PACKAGE_FORMAT_OPTIONS = [
        Constant::PACKAGE_FORMAT_NORMAL    => 'Normal',
        Constant::PACKAGE_FORMAT_LARGE     => 'Large',
        Constant::PACKAGE_FORMAT_AUTOMATIC => 'Automatic',
    ];
    public const PACKAGE_TYPE_OPTIONS   = [
        [
            'id'    => AbstractConsignment::PACKAGE_TYPE_PACKAGE,
            'name'  => AbstractConsignment::PACKAGE_TYPE_PACKAGE_NAME,
            'human' => 'Parcel',
        ],
        [
            'id'    => AbstractConsignment::PACKAGE_TYPE_MAILBOX,
            'name'  => AbstractConsignment::PACKAGE_TYPE_MAILBOX_NAME,
            'human' => 'Mailbox package',
        ],
        [
            'id'    => AbstractConsignment::PACKAGE_TYPE_LETTER,
            'name'  => AbstractConsignment::PACKAGE_TYPE_LETTER_NAME,
            'human' => 'Letter',
        ],
        [
            'id'    => AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP,
            'name'  => AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
            'human' => 'Digital stamp',
        ],
    ];

    /**
     * @var \MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier
     */
    private $carrier;

    /**
     * @var string
     */
    private $country;

    /**
     * @var \MyParcelNL\PrestaShop\Module\Carrier\ExclusiveField
     */
    private $exclusiveField;

    /**
     * @var \MyParcelNL
     */
    private $module;

    /**
     * @param  string|int|\MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier $myParcelCarrier
     * @param  null|string                                                  $country
     *
     * @throws \Exception
     */
    public function __construct($myParcelCarrier, ?string $country = null)
    {
        $this->module         = MyParcelNL::getModule();
        $this->carrier        = CarrierFactory::create($myParcelCarrier);
        $this->country        = $country ?? ModuleService::getModuleCountry();
        $this->exclusiveField = new ExclusiveField();
    }

    /**
     * @param  null|string $prefix
     *
     * @return array
     */
    public function getAvailablePackageFormats(?string $prefix = null): array
    {
        return $this->getAvailable(self::PACKAGE_FORMAT_OPTIONS, $prefix . Constant::PACKAGE_FORMAT_CONFIGURATION_NAME);
    }

    /**
     * @param  null|string $prefix
     *
     * @return array
     */
    public function getAvailablePackageTypeNames(string $prefix = null): array
    {
        $calculator = new PackageTypeCalculator();

        return array_map(static function (array $option) use ($calculator) {
            $option['value'] = $calculator->convertToName($option['value']);

            return $option;
        }, $this->getAvailablePackageTypes($prefix));
    }

    /**
     * @param  null|string $prefix
     *
     * @return array
     */
    public function getAvailablePackageTypes(string $prefix = null): array
    {
        return array_filter(self::PACKAGE_TYPE_OPTIONS, function (array $packageType) use ($prefix) {
            return $this->exclusiveField->isAvailable(
                $this->country,
                $this->carrier->getName(),
                $prefix . Constant::PACKAGE_TYPE_CONFIGURATION_NAME,
                $packageType['id']
            );
        });
    }

    /**
     * @param  array  $array
     * @param  string $setting
     *
     * @return array
     */
    private function getAvailable(array $array, string $setting): array
    {
        $available = [];

        foreach ($array as $key => $label) {
            if (! $this->exclusiveField->isAvailable($this->country, $this->carrier->getName(), $setting, $key)) {
                continue;
            }

            $available[] = [
                'value' => $key,
                'label' => $this->module->l($label, $this->module->name),
            ];
        }

        return $available;
    }
}
