<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(UserRepository $userRepository): Response
    {
        $user = $this->getUser();

       $person = $user->getPerson();

        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'person' => $person,
        ]);
    }
}