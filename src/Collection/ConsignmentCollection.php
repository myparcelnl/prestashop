<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Collection;

use Gett\MyparcelBE\Service\Platform\PlatformServiceFactory;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;

/**
 * Replacement of MyParcelCollection which ensures a user agent is set for every request.
 */
class ConsignmentCollection extends MyParcelCollection
{
    /**
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \Exception
     */
    public function createConcepts(): MyParcelCollection
    {
        $this->setUserAgents(self::getPlatformUserAgents());
        return parent::createConcepts();
    }

    /**
     * @param  int $size
     *
     * @return \MyParcelNL\Sdk\src\Helper\MyParcelCollection
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \Exception
     */
    public function setLatestData($size = 300): MyParcelCollection
    {
        $this->setUserAgents(self::getPlatformUserAgents());
        return parent::setLatestData($size);
    }

    /**
     * @throws \Exception
     */
    private static function getPlatformUserAgents(): array
    {
        return PlatformServiceFactory::create()
            ->getUserAgents();
    }
}
