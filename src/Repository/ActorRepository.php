<?php

namespace App\Repository;

use App\Entity\Actor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Actor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Actor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Actor[]    findAll()
 * @method Actor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Actor::class);
    }

    public function getAllActor($filters = [])
    {
//      dd($filters);
        $qb = $this->_em->createQueryBuilder();

        $qb->select('a')
            ->from(Actor::class, 'a')
            ->leftJoin('a.posts', 'p')
//            ->leftJoin('p.categories', 'c')
//            ->where('p.isDeleted = 0')
        ;

        if (array_key_exists('name', $filters) and $filters['name'] != "") {
//            dd("aa");
            $qb->andWhere('a.name LIKE :actor')->setParameter('actor', '%' . $filters['name'] . '%');
        }
        if (array_key_exists('post', $filters) and $filters['post'] != "") {
            $qb->andWhere('p.title LIKE :title')->setParameter('title', '%' . $filters['post'] . '%');
        }
//dd($qb->getParameters());
        return $qb;
    }
}
