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

        // company
        for ($i = 0; $i < 20; ++$i) {
            $address = new Address();
            $address->setNbStreet($this->faker->buildingNumber());
            $address->setStreet($this->faker->streetName());
            $address->setZipCode($this->faker->postcode());
            $address->setCity($this->faker->city());
            $address->setCreatedAt($date);
            $manager->persist($address);
            $company = new Company();
            $company->setName($this->faker->company());
            $company->setAddress($address);
            $company->setCreatedAt($date);
            $manager->persist($company);
            $listCompany[] = $company;
        }
        // school
        for ($i = 0; $i < 20; ++$i) {
            $address = new Address();
            $address->setNbStreet($this->faker->buildingNumber());
            $address->setStreet($this->faker->streetName());
            $address->setZipCode($this->faker->postcode());
            $address->setCity($this->faker->city());
            $address->setCreatedAt($date);
            $manager->persist($address);
            $school = new School();
            $school->setName($this->faker->company());
            $school->setAddress($address);
            $school->setCreatedAt($date);
            $manager->persist($school);
            $listSchool[] = $school;
        }

        // person
        // admin
        $admin = new Person();
        $admin->setFirstName('admin');
        $admin->setLastName('Admined');
        $admin->setRoles(['ROLE_SUPER_ADMIN']);
        $admin->setCreatedAt($date);
        $manager->persist($admin);
        $user = new User();
        $user->setEmail('admin@admin.fr');
        $user->setPassword($this->hasher->hashPassword($user, 'admin'));
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setCanLogin(true);
        $user->setPerson($admin);
        $user->setCreatedAt($date);
        $user->setEverLoggedIn(true);
        $manager->persist($user);

        // person
        // school_internship
        for ($i = 0; $i < 5; ++$i) {
            $person = new Person();
            $person->setFirstName($this->faker->firstName());
            $person->setLastName($this->faker->lastName());
            $person->setRoles(['ROLE_SCHOOL_INTERNSHIP']);
            $person->setCreatedAt($date);
            $person->setSchool($listSchool[array_rand($listSchool)]);
            $manager->persist($person);
            $listPerson[] = $person;
            $user = new User();
            $user->setEmail('schoolinternship'.$i + 1 .'@user.fr');
            $user->setPassword($this->hasher->hashPassword($user, 'user'));
            $user->setRoles(['ROLE_SCHOOL_INTERNSHIP']);
            $user->setCanLogin(true);
            $user->setPerson($person);
            $user->setCreatedAt($date);
            $user->setEverLoggedIn(false);
            $manager->persist($user);
            $listSchoolInternship[] = $user;
        }

        // person
        // manager
        for ($i = 0; $i < 5; ++$i) {
            $person = new Person();
            $person->setFirstName($this->faker->firstName());
            $person->setLastName($this->faker->lastName());
            $person->setRoles(['ROLE_ADMIN']);
            $person->setCreatedAt($date);
            $person->setCompany($listCompany[array_rand($listCompany)]);
            $manager->persist($person);
            $listPerson[] = $person;
            $user = new User();
            $user->setEmail('manager'.$i + 1 .'@user.fr');
            $user->setPassword($this->hasher->hashPassword($user, 'user'));
            $user->setRoles(['ROLE_ADMIN']);
            $user->setCanLogin(true);
            $user->setPerson($person);
            $user->setCreatedAt($date);
            $user->setEverLoggedIn(false);
            $manager->persist($user);
            $listManager[] = $user;
        }

        // person
        // company_internship
        for ($i = 0; $i < 5; ++$i) {
            $person = new Person();
            $person->setFirstName($this->faker->firstName());
            $person->setLastName($this->faker->lastName());
            $person->setRoles(['ROLE_COMPANY_INTERNSHIP']);
            $person->setCreatedAt($date);
            $person->setCompany($listCompany[array_rand($listCompany)]);
            $person->setManager($listManager[array_rand($listManager)]->getPerson());
            $manager->persist($person);
            $listPerson[] = $person;
            $user = new User();
            $user->setEmail('companyinternship'.$i + 1 .'@user.fr');
            $user->setPassword($this->hasher->hashPassword($user, 'user'));
            $user->setRoles(['ROLE_COMPANY_INTERNSHIP']);
            $user->setCanLogin(true);
            $user->setPerson($person);
            $user->setCreatedAt($date);
            $user->setEverLoggedIn(false);
            $manager->persist($user);
            $listCompanyInternship[] = $user;
        }

        // person
        // company_referent
        for ($i = 0; $i < 5; ++$i) {
            $person = new Person();
            $person->setFirstName($this->faker->firstName());
            $person->setLastName($this->faker->lastName());
            $person->setRoles(['ROLE_COMPANY_REFERENT']);
            $person->setCreatedAt($date);
            $person->setCompany($listCompany[array_rand($listCompany)]);
            $person->setManager($listManager[array_rand($listManager)]->getPerson());
            $manager->persist($person);
            $listPerson[] = $person;
            $user = new User();
            $user->setEmail('companyreferent'.$i + 1 .'@user.fr');
            $user->setPassword($this->hasher->hashPassword($user, 'user'));
            $user->setRoles(['ROLE_COMPANY_REFERENT']);
            $user->setCanLogin(true);
            $user->setPerson($person);
            $user->setCreatedAt($date);
            $user->setEverLoggedIn(false);
            $manager->persist($user);
            $listCompanyReferent[] = $user;
        }

        // person
        // trainee

        $roles = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TRAINEE', 'ROLE_SCHOOL_INTERNSHIP', 'ROLE_COMPANY_INTERNSHIP', 'ROLE_COMPANY_REFERENT'];
        for ($i = 0; $i < 30; ++$i) {
            $person = new Person();
            $person->setFirstName($this->faker->firstName());
            $person->setLastName($this->faker->lastName());
            $person->setRoles(['ROLE_TRAINEE']);
            $person->setCreatedAt($date);
            $person->setSchoolSupervisor($listSchoolInternship[array_rand($listSchoolInternship)]->getPerson());
            $person->setInternshipSupervisor($listCompanyInternship[array_rand($listCompanyInternship)]->getPerson());
            $person->setManager($listManager[array_rand($listManager)]->getPerson());
            $person->setCompanyReferent($listCompanyReferent[array_rand($listCompanyReferent)]->getPerson());
            $manager->persist($person);
            $listPerson[] = $person;

            $user = new User();
            $user->setEmail('user'.$i + 1 .'@user.fr');
            $user->setPassword($this->hasher->hashPassword($user, 'user'));
            $user->setRoles(['ROLE_TRAINEE']);
            $user->setCanLogin(true);
            $user->setPerson($person);
            $user->setCreatedAt($date);
            $user->setEverLoggedIn(false);
            $user->getPerson()->setSchool($listSchool[array_rand($listSchool)]);
            $user->getPerson()->setCompany($listCompany[array_rand($listCompany)]);

            $manager->persist($user);
            $listUser[] = $user;
        }
        $manager->flush();
    }
}
