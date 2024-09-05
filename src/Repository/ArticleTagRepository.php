<?php

namespace App\Repository;

use App\Entity\ArticleTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleTag>
 *
 * @method ArticleTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleTag[]    findAll()
 * @method ArticleTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleTag::class);
    }

    //    /**
    //     * @return ArticleTag[] Returns an array of ArticleTag objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ArticleTag
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
