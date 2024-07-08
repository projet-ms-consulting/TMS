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

class TestFixtures extends Fixture
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

        


        $manager->flush();
    }
}
