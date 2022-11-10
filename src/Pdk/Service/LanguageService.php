<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Service;

use Context;
use MyParcelNL;
use MyParcelNL\Pdk\Language\Service\AbstractLanguageService;

class LanguageService extends AbstractLanguageService
{
    /**
     * @param  null|string $language
     *
     * @return string
     */
    public function getFilePath(?string $language = null): string
    {
        $language   = $language ?? $this->getLanguage();
        $modulePath = MyParcelNL::getModule()
            ->getLocalPath();

        return sprintf("%sconfig/pdk/translations/%s.json", $modulePath, $language);
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return Context::getContext()->language->iso_code;
    }

    /**
     * @param  string      $key
     * @param  null|string $language
     *
     * @return string
     */
    public function translate(string $key, ?string $language = null): string
    {
        return MyParcelNL::getModule()
            ->l($key);
    }
}
