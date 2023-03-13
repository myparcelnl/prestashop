<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Service;

use Context;
use MyParcelNL;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Language\Service\AbstractLanguageService;
use function _HumbugBoxcbe25c660cef\RingCentral\Psr7\str;

class LanguageService extends AbstractLanguageService
{
    /**
     * @param  null|string $language
     *
     * @return string
     */
    protected function getFilePath(?string $language = null): string
    {
        $appInfo    = Pdk::getAppInfo();
        $language   = $language ?? $this->getLanguage();

        return sprintf("%sconfig/pdk/translations/%s.json", $appInfo['path'], $language);
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return Context::getContext()->language->iso_code;
    }
}
