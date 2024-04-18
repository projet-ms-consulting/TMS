<?php

namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\User;
use DateTimeImmutable;
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
    public function load(ObjectManager $manager): void
    {
        $date = new DateTimeImmutable($this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d H:i:s'));

        $person = new Person();
        $person->setFirstName('John');
        $person->setLastName('Doe');
        $person->setStartInternship($date);
        $person->setCreatedAt($date);
        $manager->persist($person);

        $user = new User();
        $user->setEmail('admin@email.com');
        $hash = $this->hasher->hashPassword($user, 'admin');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setPassword($hash);
        $user->setCreatedAt($date);
        $user->setPerson($person);
        $manager->persist($user);

        $manager->flush();
    }
}
