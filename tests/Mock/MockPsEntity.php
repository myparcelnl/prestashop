<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use ObjectModel;
use RuntimeException;

abstract class MockPsEntity extends ObjectModel
{
    /**
     * @param  null|int $id
     * @param  null|int $id_lang
     * @param  null|int $id_shop
     * @param  null|int $translator
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function __construct(?int $id = null, ?int $id_lang = null, ?int $id_shop = null, ?int $translator = null)
    {
        parent::__construct($id, $id_lang, $id_shop, $translator);

        if ($id) {
            $this->fromModel(MockPsEntities::get($id));
        }
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    private function fromModel(?ObjectModel $objectModel): void
    {
        if (! $objectModel instanceof static) {
            throw new RuntimeException('Given object is not an instance of ' . static::class);
        }

        $this->fill($objectModel->getAttributes());
    }
}
