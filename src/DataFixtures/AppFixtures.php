<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\Person;
use App\Entity\School;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->faker = Factory::create('fr_FR');
        $this->hasher = $hasher;
    }

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $date = new \DateTimeImmutable($this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d H:i:s'));

        $address = new Address();
        $address->setStreet('rue de la paix');
        $address->setZipCode('75001');
        $address->setCity('paris');
        $address->setCreatedAt($date);
        $manager->persist($address);

        $school = new School();
        $school->setName('ENI école');
        $school->setAddress($address);
        $manager->persist($school);

        $company = new Company();
        $company->setName('MS Consulting');
        $company->setAddress($address);
        $manager->persist($company);

        $person = new Person();
        $person->setFirstName('John');
        $person->setLastName('Doe');
        $person->setStartInternship($date);
        $person->setCreatedAt($date);
        $manager->persist($person);

        $user = new User();
        $user->setEmail('super_admin@email.com');
        $hash = $this->hasher->hashPassword($user, 'admin');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setPassword($hash);
        $user->setCreatedAt($date);
        $user->setPerson($person);
        $manager->persist($user);

        $person2 = new Person();
        $person2->setFirstName('Michael');
        $person2->setLastName('Sanchez');
        $person2->setCreatedAt($date);
        $manager->persist($person2);

        $user2 = new User();
        $user2->setEmail('michael.sanchez@msconsulting-europe.com');
        $hash = $this->hasher->hashPassword($user2, 'super_admin');
        $user2->setRoles(['ROLE_SUPER_ADMIN']);
        $user2->setPassword($hash);
        $user2->setCreatedAt($date);
        $user2->setPerson($person2);
        $manager->persist($user2);

        $person3 = new Person();
        $person3->setFirstName('Julien');
        $person3->setLastName('Paul');
        $person3->setCreatedAt($date);
        $manager->persist($person3);

        $user3 = new User();
        $user3->setEmail('julien.sanchez@msconsulting-europe.com');
        $hash = $this->hasher->hashPassword($user3, 'super_admin');
        $user3->setRoles(['ROLE_SUPER_ADMIN']);
        $user3->setPassword($hash);
        $user3->setCreatedAt($date);
        $user3->setPerson($person3);
        $manager->persist($user3);

        $person4 = new Person();
        $person4->setFirstName('La quatrième');
        $person4->setLastName('Personne');
        $person4->setCreatedAt($date);
        $manager->persist($person4);

        $user4 = new User();
        $user4->setEmail('4.user@email.com');
        $hash = $this->hasher->hashPassword($user4, 'user');
        $user4->setRoles(['ROLE_ADMIN']);
        $user4->setPassword($hash);
        $user4->setCreatedAt($date);
        $user4->setPerson($person4);
        $manager->persist($user4);

        $person5 = new Person();
        $person5->setFirstName('La cinquième');
        $person5->setLastName('Personne');
        $person5->setCreatedAt($date);
        $person5->setSchool($school);
        $manager->persist($person5);

        $user5 = new User();
        $user5->setEmail('5.user@email.com');
        $hash = $this->hasher->hashPassword($user5, 'user');
        $user5->setRoles(['ROLE_SCHOOL_INTERNSHIP']);
        $user5->setPassword($hash);
        $user5->setCreatedAt($date);
        $user5->setPerson($person5);
        $manager->persist($user5);

        $person6 = new Person();
        $person6->setFirstName('La sixième');
        $person6->setLastName('Personne');
        $person6->setCreatedAt($date);
        $person6->setCompany($company);
        $manager->persist($person6);

        $user6 = new User();
        $user6->setEmail('6.user@email.com');
        $hash = $this->hasher->hashPassword($user6, 'user');
        $user6->setRoles(['ROLE_COMPANY_INTERNSHIP']);
        $user6->setPassword($hash);
        $user6->setCreatedAt($date);
        $user6->setPerson($person6);
        $manager->persist($user6);

        $person7 = new Person();
        $person7->setFirstName('La septième');
        $person7->setLastName('Personne');
        $person7->setStartInternship($date);
        $person7->setCreatedAt($date);
        $person7->setCompany($company);
        $person7->setSchool($school);
        $manager->persist($person7);

        $user7 = new User();
        $user7->setEmail('7.user@email.com');
        $hash = $this->hasher->hashPassword($user7, 'user');
        $user7->setRoles(['ROLE_TRAINEE']);
        $user7->setPassword($hash);
        $user7->setCreatedAt($date);
        $user7->setPerson($person7);
        $manager->persist($user7);



        $manager->flush();
    }
}
