<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\User;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('', name: 'app_')]
class HomeController extends AbstractController
{
    #[Route('', name: 'accueil')]
    public function index(PersonRepository $personRepository, Security $security): Response
    {
        $user = $this->getUser();
        $person = $personRepository->find($user->getPerson()->getId());
        return $this->render('home/index.html.twig', [
            'person' => $person,
        ]);
    }
}
