<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

abstract class MockPsLanguage extends BaseMock
{
    private const DEFAULT_PROPERTIES = [
        'active'           => 1,
        'is_rtl'           => 0,
        'date_format_lite' => 'd-m-Y',
        'date_format_full' => 'd-m-Y H:i:s',
        'id_shop'          => 1,
        'id_shop_list'     => [1],
    ];

    public static function getLanguages(): array
    {
        return [
            array_replace(self::DEFAULT_PROPERTIES, [
                'id_lang'       => 1,
                'name'          => 'English (English)',
                'iso_code'      => 'en',
                'language_code' => 'en-us',
                'locale'        => 'en-US',
            ]),
        ];
    }
}
