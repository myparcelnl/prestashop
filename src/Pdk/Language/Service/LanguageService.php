<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Language\Service;

use Context;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Language\Service\AbstractLanguageService;

class LanguageService extends AbstractLanguageService
{
    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return Context::getContext()->language->iso_code;
    }

    /**
     * @param  null|string $language
     *
     * @return string
     */
    protected function getFilePath(?string $language = null): string
    {
        $appInfo  = Pdk::getAppInfo();
        $language = $language ?? $this->getLanguage();

        return sprintf("%sconfig/pdk/translations/%s.json", $appInfo['path'], $language);
    }
}
