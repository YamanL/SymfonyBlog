<?php

namespace App\Repository;

use App\Entity\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rating|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rating|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rating[]    findAll()
 * @method Rating[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    // example custom query
//    public function findRatingsForPost($postId)
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.post = :val')
//            ->setParameter('val', $postId)
//            ->orderBy('r.createdAt', 'DESC')
//            ->getQuery()
//            ->getResult()
//            ;
//    }

}
