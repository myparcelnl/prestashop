<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use Db;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;

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
