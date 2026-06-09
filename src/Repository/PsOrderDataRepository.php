<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use Doctrine\ORM\EntityRepository;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;

/**
 * @extends AbstractPsObjectRepository<MyparcelnlOrderData>
 */
final class PsOrderDataRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlOrderData::class;

    /**
     * Order data is keyed by the order id (it is the entity's primary key), so findAll() matches
     * against orderId rather than the default 'id'.
     *
     * @return string
     */
    protected function getIdentifierColumn(): string
    {
        return 'orderId';
    }

    /**
     * @param  string $apiIdentifier
     *
     * @return null|\MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData
     */
    public function findOneByApiIdentifier(string $apiIdentifier): ?MyparcelnlOrderData
    {
        // The test mock repository exposes createQueryBuilder for batched-iteration
        // scenarios but does not implement DQL where/parameter binding, so route
        // any non-Doctrine repository through an in-memory match instead.
        if (! $this->entityRepository instanceof EntityRepository) {
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
