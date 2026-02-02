<?php
/** @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use DI\Definition\Helper\FactoryDefinitionHelper;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use MyParcelNL\PrestaShop\Pdk\Base\PsPdkBootstrapper;
use MyParcelNL\PrestaShop\Tests\Bootstrap\MockPsPdkBootstrapper;
use MyParcelNL\Sdk\Support\Arr;
use function DI\factory;

if (! function_exists('\MyParcelNL\PrestaShop\bootPdk')) {
    /**
     * @param  string $version
     * @param  string $path
     * @param  string $url
     * @param  string $mode
     *
     * @return void
     * @throws \Exception
     */
    function bootPdk(
        string $version,
        string $path,
        string $url,
        string $mode = Pdk::MODE_PRODUCTION
    ): void {
        // TODO: find a way to make this work without having this in production code
        if (! defined('PEST')) {
            PsPdkBootstrapper::boot(...func_get_args());

            return;
        }

        MockPsPdkBootstrapper::boot(...func_get_args());
    }
}

/**
 * @template-covariant T
 * @param  array{version: string, operator: string, class: class-string<T>}[] $entries
 *
 * @return \DI\Definition\Helper\FactoryDefinitionHelper
 */
function psVersionFactory(array $entries): FactoryDefinitionHelper
{
    return factory(function (array $entries) {
        $psVersion = PdkFacade::get('ps.version');
        $fallback  = Arr::first($entries, static function ($entry) {
            return ! isset($entry['version']);
        });

        foreach ($entries as $item) {
            if (! isset($item['version'])) {
                continue;
            }

            $operator = $item['operator'] ?? '>=';

            if (version_compare($psVersion, (string) $item['version'], $operator)) {
                return PdkFacade::get($item['class']);
            }
        }

        return PdkFacade::get($fallback['class']);
    })->parameter('entries', $entries);
}
