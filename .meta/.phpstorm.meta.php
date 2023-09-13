<?php

/**
 * @see https://www.jetbrains.com/help/phpstorm/ide-advanced-metadata.html
 */

namespace PHPSTORM_META {

    // Factories

    override(\MyParcelNL\PrestaShop\psFactory(), map(['' => '@Factory']));
    override(\MyParcelNL\PrestaShop\Tests\Factory\PsFactoryFactory::create(), map(['' => '@Factory']));
}
