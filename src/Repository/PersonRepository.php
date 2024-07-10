<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Person>
 *
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Person::class);
    }

    // Filtre des personnes internes à l'école
    public function filterSchoolInternshipPersons(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_SCHOOL_INTERNSHIP"%')
            ->getQuery()
            ->getResult();
    }

    // Filtre des personnes internes à l'entreprise
    public function filterCompanyEmployeePersons(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->where('u.roles LIKE :role1 OR u.roles LIKE :role2')
            ->andWhere('u.roles NOT LIKE :excludedRole')
            ->setParameter('role1', '%"ROLE_COMPANY_INTERNSHIP"%')
            ->setParameter('role2', '%"ROLE_ADMIN"%')
            ->setParameter('excludedRole', '%"ROLE_TRAINEE"%')
            ->getQuery()
            ->getResult();
    }

    // Filtre des personnes stagiaires
    public function filterTraineePersons(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_TRAINEE"%')
            ->getQuery()
            ->getResult();
    }

    public function paginatePerson(int $page, int $limit): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p')
                ->select('p'),
            $page,
            $limit,
            [
                'defaultSortFieldName' => 'p.id',
                'defaultSortDirection' => 'asc',
                'sortFieldWhitelist' => ['p.id', 'p.firstName', 'p.lastName', 'p.createdAt', 'p.updatedAt'],
            ]
        );
    }

    public function paginateSchoolEmployee(int $page, int $limit): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->innerJoin('p.school', 's')
            ->addSelect('s')
            ->innerJoin('s.address', 'a')
            ->addSelect('a')
            ->innerJoin('p.user', 'u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_SCHOOL_INTERNSHIP"%');

        $query = $qb->getQuery();

        return $this->paginator->paginate(
            $query,
            $page,
            $limit,
            [
                'defaultSortFieldName' => 'p.id',
                'defaultSortDirection' => 'asc',
                'sortFieldWhitelist' => ['p.id', 'p.firstName', 'p.lastName', 'p.updatedAt', 's.name', 'a.nbStreet', 'a.street', 'a.zipCode', 'a.city', 'p.mailPro'],
            ]
        );
    }

    public function paginateCompanyEmployee(Query $query, int $page, int $limit): PaginationInterface
    {
        return $this->paginator->paginate(
            $query,
            $page,
            $limit,
            [
                'defaultSortFieldName' => 'p.id',
                'defaultSortDirection' => 'asc',
                'sortFieldWhitelist' => ['p.id', 'p.firstName', 'p.lastName', 'p.updatedAt', 'p.company.name', 'p.company.address.FullAddress', 'p.mailPro'],
            ]
        );
    }

    public function paginateTrainee(Query $query, int $page, int $limit): PaginationInterface
    {
        return $this->paginator->paginate(
            $query,
            $page,
            $limit,
            [
                'defaultSortFieldName' => 'p.id',
                'defaultSortDirection' => 'asc',
                'sortFieldWhitelist' => ['p.id', 'p.firstName', 'p.lastName', 'p.updatedAt', 'p.school.name', 'p.school.address.FullAddress', 'p.mailPro', 'p.company.name', 'p.company.address.FullAddress', 'p.mailPerso'],
            ]
        );
    }

    //    /**
    //     * @return Person[] Returns an array of Person objects
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

    //    public function findOneBySomeField($value): ?Person
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
