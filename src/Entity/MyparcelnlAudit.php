<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Entity\Concern\HasCreatedTimestamps;
use MyParcelNL\PrestaShop\Entity\Concern\HasJsonData;
use MyParcelNL\PrestaShop\Entity\Contract\EntityWithCreatedTimestampsInterface;

/**
 * @ORM\Table
 * @ORM\Entity
 * @see \MyParcelNL\PrestaShop\Database\CreateAuditTableDatabaseMigration
 * @final
 */
class MyparcelnlAudit extends AbstractEntity implements EntityWithCreatedTimestampsInterface
{
    use HasJsonData;
    use HasCreatedTimestamps;

    // * @property null|string              $id
    // * @property array                    $arguments
    // * @property string                   $type
    // * @property null|string              $action
    // * @property null|class-string<Model> $model
    // * @property null|string              $modelIdentifier
    // * @property null|\DateTime           $created

    /**
     * @var string
     * @ORM\Column(name="action", type="string")
     */
    private $action;

    /**
     * @var string
     * @ORM\Column(name="id", type="string")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="model", type="string")
     */
    private $model;

    /**
     * @var string
     * @ORM\Column(name="model_identifier", type="string")
     */
    private $modelIdentifier;

    public static function getTable(): string
    {
        return Table::TABLE_AUDITS;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getModelIdentifier(): string
    {
        return $this->modelIdentifier;
    }

    public function toArray(?int $flags = null): array
    {
        return [
            'id'              => $this->getId(),
            'action'          => $this->getAction(),
            'model'           => $this->getModel(),
            'modelIdentifier' => $this->getModelIdentifier(),
            'data'            => $this->getData(),
            'dateAdd'         => $this->getDateAdd(),
        ];
    }
}
