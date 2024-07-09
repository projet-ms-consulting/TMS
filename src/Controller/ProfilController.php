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

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

       $userFromDatabase = $userRepository->find($user->getId());

        $person = $userFromDatabase->getPerson();

        return $this->render('profil/index.html.twig', [
            'user' => $userFromDatabase,
            'person' => $person,
        ]);
    }
}