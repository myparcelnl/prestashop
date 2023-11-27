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

    /**
     * @var string
     * @ORM\Column(name="action", type="string")
     */
    private $action;

    /**
     * @var string
     * @ORM\Id
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

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function setModelIdentifier(string $modelIdentifier): void
    {
        $this->modelIdentifier = $modelIdentifier;
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
