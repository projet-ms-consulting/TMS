<?php

namespace App\Controller;

use App\Repository\PersonRepository;
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
        $person = $user->getPerson();

        return $this->render('home/index.html.twig', [
            'person' => $person,
        ]);
    }
}
