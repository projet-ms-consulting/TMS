<?php

namespace App\Controller\super_admin;

use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/super_admin', name: 'super_admin_')]
class HomeController extends AbstractController
{
    #[Route('', name: 'home')]
    public function index(PersonRepository $personRepository, Security $security): Response
    {
        $user = $this->getUser();
        $person = $user->getPerson();

        return $this->render('super_admin/home/index.html.twig', [
            'person' => $person,
        ]);
    }
}
