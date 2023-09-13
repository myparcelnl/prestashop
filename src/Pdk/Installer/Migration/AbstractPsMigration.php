<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Migration;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

abstract class AbstractPsMigration implements MigrationInterface
{
    /**
     * @var \Db
     */
    protected $db;

    public function __construct()
    {
        $this->db = Db::getInstance();
    }
}
