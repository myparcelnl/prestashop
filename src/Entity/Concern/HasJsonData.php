<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\Concern;

trait HasJsonData
{
    /**
     * @var string
     * @Doctrine\ORM\Mapping\Column(type="text", nullable=false)
     */
    public $data;

    /**
     * @return array
     */
    public function getData(): array
    {
        return json_decode($this->data, true);
    }
}
