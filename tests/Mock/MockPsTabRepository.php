<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use Tab;

/**
 * @see \PrestaShopBundle\Entity\Repository\TabRepository
 */
final class MockPsTabRepository extends MockPsEntityRepository
{
    public function __construct()
    {
        parent::__construct(Tab::class);
    }

    /**
     * @param  string $moduleName
     *
     * @return Tab[]
     */
    public function findByModule(string $moduleName): array
    {
        return $this
            ->findBy(['module' => $moduleName])
            ->all();
    }

    /**
     * @param  int $idParent
     *
     * @return Tab[]
     */
    public function findByParentId(int $idParent): array
    {
        return $this
            ->findBy(['idParent' => $idParent])
            ->all();
    }

    /**
     * @param  string $className
     *
     * @return Tab|null
     */
    public function findOneByClassName(string $className): ?Tab
    {
        return $this->findOneBy(['className' => $className]);
    }

    /**
     * @param  string $className
     *
     * @return int|null
     */
    public function findOneIdByClassName(string $className)
    {
        $tab = $this->findOneByClassName($className);

        if ($tab) {
            return $tab->getId();
        }

        return null;
    }
}
