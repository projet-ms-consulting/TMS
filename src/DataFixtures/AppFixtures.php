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

        //address
        for ($i = 0; $i < 30; $i++) {
            $address = new Address();
            $address->setNbStreet($this->faker->buildingNumber());
            $address->setStreet($this->faker->streetName());
            $address->setZipCode($this->faker->postcode());
            $address->setCity($this->faker->city());
            $address->setCreatedAt($date);
            $manager->persist($address);
            $listAddress[] = $address;
        }

        //company
        for ($i = 0; $i < 20; $i++) {
            $company = new Company();
            $company->setName($this->faker->company());
            $company->setAddress($listAddress[array_rand($listAddress)]);
            $company->setCreatedAt($date);
            $manager->persist($company);
            $listCompany[] = $company;
        }

        //person
        //admin
        $admin = new Person();
        $admin->setFirstName('admin');
        $admin->setLastName('Admined');
        $admin->setCreatedAt($date);
        $manager->persist($admin);
        $user = new User();
        $user->setEmail('admin@admin.fr');
        $user->setPassword($this->hasher->hashPassword($user, 'admin'));
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setPerson($admin);
        $user->setCreatedAt($date);
        $manager->persist($user);

        $roles = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TRAINEE', 'ROLE_SCHOOL_INTERNSHIP', 'ROLE_COMPANY_INTERNSHIP'];
        for ($i = 0; $i < 30; $i++) {
            $person = new Person();
            $person->setFirstName($this->faker->firstName());
            $person->setLastName($this->faker->lastName());
            $person->setCreatedAt($date);
            $manager->persist($person);
            $listPerson[] = $person;
            $user = new User();
            $user->setEmail('user' . $i + 1 . '@user.fr');
            $user->setPassword($this->hasher->hashPassword($user, 'user'));
            $user->setRoles([$roles[array_rand($roles)]]);
            $user->setPerson($person);
            $user->setCreatedAt($date);
            $manager->persist($user);
            $listUser[] = $user;
        }

        $manager->flush();
    }
}
