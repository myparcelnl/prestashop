<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Migration;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

abstract class AbstractPsMigration implements MigrationInterface
{
    /**
     * @var \PrestaShop\PrestaShop\Adapter\Entity\Db
     */
    protected $db;

    public function __construct()
    {
        $this->db = Db::getInstance();
    }
}
