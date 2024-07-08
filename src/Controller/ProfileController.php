<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     */
    public function showProfile(): Response
    {
        $user = $this->getUser();
        // $profileInfo = $this->getDoctrine()->getRepository(User::class)->find($user->getId());


        return $this->render('profile/show.html.twig', [
            'user' => $user,

        ]);
    }
}

