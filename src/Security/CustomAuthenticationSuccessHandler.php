<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class CustomAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?\Symfony\Component\HttpFoundation\Response
    {
        $user = $token->getUser();

        if (!$user->isEverLoggedIn()) {
            $user->setEverLoggedIn(true);
            // Vous devez persister l'entité utilisateur ici

            $url = $this->urlGenerator->generate('app_reset_password_firstLogin');
        } else {
            $url = $this->urlGenerator->generate('app_home');
        }

        return new RedirectResponse($url);
    }
}
