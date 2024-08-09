<?php

namespace App\Repository;

use App\Entity\Company;
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
    public function filterCompanyEmployeePersons(): Query
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->where('u.roles LIKE :role1 OR u.roles LIKE :role2')
            ->andWhere('u.roles NOT LIKE :excludedRole')
            ->setParameter('role1', '%"ROLE_COMPANY_INTERNSHIP"%')
            ->setParameter('role2', '%"ROLE_ADMIN"%')
            ->setParameter('excludedRole', '%"ROLE_TRAINEE"%')
            ->getQuery();
    }

    // Filtre des personnes stagiaires
    public function filterTraineePersonsPerCompany(Company $company): Query
    {
        $companyId = $company->getId();

        return $this->createQueryBuilder('p')
            ->leftJoin('p.company', 'c')
            ->where('p.roles LIKE :role')
            ->andWhere('c.id = :companyId')
            ->setParameter('role', '%"ROLE_TRAINEE"%')
            ->setParameter('companyId', $companyId)
            ->getQuery();
    }

    // Filtre des personnes internes à une entreprise
    public function filterInternshipPerCompany(Company $company): Query
    {
        $companyId = $company->getId();

        return $this->createQueryBuilder('p')
            ->leftJoin('p.company', 'c')
            ->where('p.roles LIKE :role1 OR p.roles LIKE :role2 OR p.roles LIKE :role3')
            ->andWhere('p.roles NOT LIKE :excludedRole')
            ->andWhere('c.id = :companyId')
            ->setParameter('role1', '%"ROLE_COMPANY_INTERNSHIP"%')
            ->setParameter('role2', '%"ROLE_ADMIN"%')
            ->setParameter('role3', '%"ROLE_COMPANY_REFERENT"%')
            ->setParameter('excludedRole', '%"ROLE_TRAINEE"%')
            ->setParameter('companyId', $companyId)
            ->getQuery();
    }

    public function paginatePerson(int $page, int $limit): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p')
                ->select('p')
                ->leftJoin('p.user', 'u')
                ->addSelect('u'),
            $page,
            $limit,
            [
                'defaultSortFieldName' => 'p.id',
                'defaultSortDirection' => 'asc',
                'sortFieldWhitelist' => ['p.id', 'p.firstName', 'p.lastName', 'p.createdAt', 'p.updatedAt', 'u.everLoggedIn'],
            ]
        );
    }

    public function paginateSchoolEmployee(int $page, int $limit): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->leftJoin('p.school', 's')
            ->addSelect('s')
            ->leftJoin('s.address', 'a')
            ->addSelect('a')
            ->leftJoin('p.user', 'u')
            ->where('u.roles LIKE :role OR p.roles LIKE :role')
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

    public function paginateCompanyEmployee(int $page, int $limit): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->leftJoin('p.company', 'c')
            ->addSelect('c')
            ->leftJoin('c.address', 'a')
            ->addSelect('a')
            ->leftJoin('p.user', 'u')
            ->where('u.roles LIKE :role OR u.roles LIKE :role2 OR u.roles LIKE :role3')
            ->Where('p.roles LIKE :role OR p.roles LIKE :role2 OR p.roles LIKE :role3')
            ->setParameter('role', '%"ROLE_COMPANY_INTERNSHIP"%')
            ->setParameter('role2', '%"ROLE_ADMIN"%')
            ->setParameter('role3', '%"ROLE_COMPANY_REFERENT"%');

        $query = $qb->getQuery();

        return $this->paginator->paginate(
            $query,
            $page,
            $limit,
            [
                'defaultSortFieldName' => 'p.id',
                'defaultSortDirection' => 'asc',
                'sortFieldWhitelist' => ['p.id', 'p.firstName', 'p.lastName', 'p.updatedAt', 'c.name', 'a.nbStreet', 'a.street', 'a.zipCode', 'a.city', 'p.mailPro', 'u.roles'],
            ]
        );
    }

    public function paginateTrainee(int $page, int $limit): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->leftJoin('p.company', 'c')
            ->addSelect('s')
            ->leftJoin('p.school', 's')
            ->addSelect('s')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.internshipSupervisor', 'i')
            ->addSelect('i')
            ->leftJoin('p.schoolSupervisor', 'ss')
            ->addSelect('ss')
            ->leftJoin('p.manager', 'm')
            ->addSelect('m')
            ->leftJoin('p.companyReferent', 'cr')
            ->where('u.roles LIKE :role OR p.roles LIKE :role')
            ->setParameter('role', '%"ROLE_TRAINEE"%')
            ->getQuery()
            ->getResult();
        $query = $qb->getQuery();

        return $this->paginator->paginate(
            $query,
            $page,
            $limit,
            [
                'defaultSortFieldName' => 'p.id',
                'defaultSortDirection' => 'asc',
                'sortFieldWhitelist' => ['p.id', 'p.firstName', 'p.lastName', 'p.updatedAt', 'ss.lastName', 'i.lastName', 'm.lastName', 's.name', 'p.mailPro', 'c.name', 'p.mailPerso', 'cr.lastName'],
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
