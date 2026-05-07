<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;

/**
 * @extends AbstractPsObjectRepository<MyparcelnlOrderData>
 */
final class PsOrderDataRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlOrderData::class;

    /**
     * @param  string $apiIdentifier
     *
     * @return null|\MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData
     */
    public function findOneByApiIdentifier(string $apiIdentifier): ?MyparcelnlOrderData
    {
        // The test repository does not implement Doctrine query builders.
        if (! method_exists($this->entityRepository, 'createQueryBuilder')) {
            return $this
                ->all()
                ->first(static function (MyparcelnlOrderData $orderData) use ($apiIdentifier): bool {
                    return ($orderData->getData()['apiIdentifier'] ?? null) === $apiIdentifier;
                });
        }

        return $this->entityRepository
            ->createQueryBuilder('orderData')
            ->where('orderData.data LIKE :apiIdentifier')
            ->setParameter('apiIdentifier', sprintf('%%"apiIdentifier":%s%%', json_encode($apiIdentifier)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
