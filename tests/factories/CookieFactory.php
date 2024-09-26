<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithCustomer;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithLang;

/**
 * @method withDetectLanguage(bool $detectLanguage)
 * @method withIdEmployee(int $idEmployee)
 * @method withIdGuest(int $idGuest)
 * @method withIdConnections(int $idConnections)
 * @method withIsGuest(bool $isGuest)
 * @method withLogged(bool $logged)
 * @method withPasswd(string $passwd)
 * @method withSessionId(int $sessionId)
 * @method withSessionToken(string $sessionToken)
 * @method withShopContext(string $shopContext)
 * @method withLastActivity(int $lastActivity)
 */
final class CookieFactory extends AbstractPsObjectModelFactory implements WithCustomer, WithLang
{
    protected function getObjectModelClass(): string
    {
        return Cookie::class;
    }
}
