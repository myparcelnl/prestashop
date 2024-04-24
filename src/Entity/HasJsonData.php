<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @TODO Move to \MyParcelNL\PrestaShop\Entity\Concern namespace when possible.
 * @see  https://github.com/myparcelnl/prestashop/issues/242
 */
trait HasJsonData
{
    /**
     * @var string
     * @ORM\Column(name="data", type="text", nullable=false)
     */
    private $data;

    /**
     * @return array
     */
    public function getData(): array
    {
        return json_decode($this->data, true);
    }

    /**
     * @param  string $data
     *
     * @return $this
     */
    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }
}
