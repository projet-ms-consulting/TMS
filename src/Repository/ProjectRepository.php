<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Project::class);
    }

    public function paginateProject(int $page, int $limit): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p')
                ->select('p')
                ->leftJoin('p.participant', 'pp')
                ->addSelect('pp')
                ->leftJoin('p.company', 'c')
                ->addSelect('c')
                ->leftJoin('p.links', 'l')
                ->addSelect('l'),

            $page,
            $limit,
            [
                'defaultSortFieldName' => 'p.id',
                'defaultSortDirection' => 'asc',
                'sortFieldWhitelist' => ['p.id', 'p.name', 'p.description', 'pp.lastName', 'c.name', 'p.updatedAt', 'l.label'],
            ]
        );
    }

    //    /**
    //     * @return Project[] Returns an array of Project objects
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

    //    public function findOneBySomeField($value): ?Project
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
