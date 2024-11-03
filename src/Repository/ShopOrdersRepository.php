<?php

namespace App\Repository;

use App\Entity\ShopOrders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopOrders>
 *
 * @method ShopOrders|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopOrders|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopOrders[]    findAll()
 * @method ShopOrders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopOrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopOrders::class);
    }

    //    /**
    //     * @return ShopOrders[] Returns an array of ShopOrders objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ShopOrders
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

