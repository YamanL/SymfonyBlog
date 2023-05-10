<?php

namespace App\Repository;

use App\Entity\Actor;
use App\Entity\Post;
use App\Entity\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);

    }

    public function getAllPost($filters = [])
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p')
            ->from(Post::class, 'p')
            ->leftJoin('p.actors', 'a')
            ->leftJoin('p.categories', 'c')

        ;

        if (array_key_exists('title', $filters) and $filters['title'] != "") {
            $qb->andWhere('p.title LIKE :name')->setParameter('name', '%' . $filters['title'] . '%');
        }
        if (array_key_exists('actor', $filters) and $filters['actor'] != "") {
            $qb->andWhere('a.name LIKE :name')->setParameter('name', '%' . $filters['actor'] . '%');
        }
        if (array_key_exists('category', $filters) and $filters['category'] != "") {
            $qb->andWhere('c.title LIKE :categories')->setParameter('categories', $filters['category']);
        }

        return $qb;
    }

}

//
//     /**
//      * @return Post[] Returns an array of Post objects
//      */
//
//    public function findByPost($post): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.title = :val')
//            ->setParameter('val', $post)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }
//
//
//
//    public function findOneByTitle($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.title = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
//
//}
