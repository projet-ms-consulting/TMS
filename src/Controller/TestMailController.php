<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class TestMailController extends AbstractController
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/test/mail', name: 'testEnvoiMail')]
    public function index(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new Email())
                ->from('noreply@msconsulting.com')
                ->to('testMail@msconsulting-europe.com')
                ->subject('Test de mail')
                ->html('<p>Ceci est un test</p>');
            $this->mailer->send($email);

            return $this->redirectToRoute('testEnvoiMail');
        }

        return $this->render('test_mail/index.html.twig', [
            'controller_name' => 'TestMailController',
            'form' => $form->createView(),
        ]);
    }
}
