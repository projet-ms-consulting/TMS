<?php

namespace App\Controller\admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
class HomeController extends AbstractController
{
    #[Route('', name: 'main')]
    public function index(): Response
    {
        return $this->render('admin/main/index.html.twig');
    }
}
