<?php

namespace App\Controller\super_admin;

use App\Entity\Links;
use App\Form\Links1Type;
use App\Repository\LinksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/links', name: 'app_super_admin_links_')]
class LinksController extends AbstractController
{

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Links $link, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$link->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($link);
            $entityManager->flush();
        }

        $referer = $request->headers->get('referer');

        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_accueil', [], Response::HTTP_SEE_OTHER);
    }
}
