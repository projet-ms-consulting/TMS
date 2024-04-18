<?php

namespace App\Repository;

use App\Entity\School;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<School>
 *
 * @method School|null find($id, $lockMode = null, $lockVersion = null)
 * @method School|null findOneBy(array $criteria, array $orderBy = null)
 * @method School[]    findAll()
 * @method School[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, School::class);
    }

    public function paginateSchools(int $page, int $limit): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('s')
                ->leftJoin('s.address', 'a')
                ->leftJoin('s.people', 'p')
                ->select('s', 'a', 'p'),
            $page,
            $limit,
            [
                'defaultSortFieldName' => 's.id',
                'defaultSortDirection' => 'asc',
                'sortFieldWhitelist' => ['s.id', 's.name', 's.createdAt', 's.updatedAt'],
            ]
        );
    }
}
