<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('', name: 'app_')]
class HomeController extends AbstractController
{
    private $security;

    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'accueil')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->security->getUser();
        $person = $entityManager->getRepository(Person::class)->find(1);
        return $this->render('home/index.html.twig', [
            'user' => $user,
            'person' => $person,
        ]);
    }

    public function home(): Response
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $person = $user->getPerson();
        } else {
            $person = null;
        }

        return $this->render('base.html.twig', [
            'user' => $user,
            'person' => $person,
        ]);
    }
}
