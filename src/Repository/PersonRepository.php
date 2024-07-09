<?php

namespace App\Repository;

use App\Entity\Person;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    //Filtre des personnes internes à l'école
    public function filterSchoolInternshipPersons(): array
    {

        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_SCHOOL_INTERNSHIP"%')
            ->getQuery()
            ->getResult();

    }

    //Filtre des personnes internes à l'entreprise
    public function filterCompanyEmployeePersons(): array
    {

        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->where('u.roles LIKE :role1 OR u.roles LIKE :role2')
            ->setParameter('role1', '%"ROLE_COMPANY_INTERNSHIP"%')
            ->setParameter('role2', '%"ROLE_ADMIN"%')
            ->getQuery()
            ->getResult();

    }

    //Filtre des personnes stagiaires
    public function filterTraineePersons(): array
    {

        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_TRAINEE"%')
            ->getQuery()
            ->getResult();
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
