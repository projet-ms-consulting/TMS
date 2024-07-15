<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[Route(path : '/reset_password', name: 'app_reset_password_')]
class ResetPasswordController extends AbstractController
{
    private $passwordHasher;
    private MailerInterface $mailer;

    private $doctrine;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer)
    {
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
        $this->mailer = $mailer;
    }

    #[Route('/reset', name: 'firstLogin')]
    public function reset(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user instanceof PasswordAuthenticatedUserInterface && false === $user->isEverLoggedIn()) {
            $form = $this->createFormBuilder()
                ->add('password', PasswordType::class)
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $password = $form->getData();

                // Encodez le nouveau mot de passe
                $encodedPassword = $this->passwordHasher->hashPassword($user, $password);

                // Mettez à jour le mot de passe de l'utilisateur dans la base de données
                $user->setPassword($encodedPassword);
                $user->setEverLoggedIn(true);
                $entityManager->persist($user);
                $entityManager->flush();

                // Redirigez l'utilisateur vers la page de connexion ou vers la page d'accueil
                return $this->redirectToRoute('app_login');
            }

            // Affichez la page de réinitialisation de mot de passe
            return $this->render('reset_password/firstLoginReset.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // Redirigez l'utilisateur vers la page d'accueil s'il a déjà été connecté
        return $this->redirectToRoute('app_home');
    }

    /**
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    #[Route('/reset-password', name: 'reset')]
    public function showResetForm(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $email = $form->getData();

                $user = $this->doctrine
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $email]);

                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $user->setPasswordResetToken($token);
                    $this->doctrine->getManager()->flush();

                    $email = (new Email())
                        ->from('noreply@msconsulting.com')
                        ->to($user->getEmail())
                        ->subject('Réinitialisation de mot de passe')
                        ->html('<p>Cliquez sur le lien suivant pour réinitialiser votre mot de passe : <a href="'.$this->generateUrl('app_reset_password_reset_with_token', ['token' => $token]).'">Réinitialiser mon mot de passe</a></p>');

                    $this->mailer->send($email);

                }
                $this->addFlash('success', 'Un e-mail de réinitialisation de mot de passe a été envoyé à votre adresse e-mail.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('reset_password/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'reset_with_token')]
    public function showResetFormWithToken(Request $request, string $token): Response
    {
        $user = $this->doctrine
            ->getRepository(User::class)
            ->findOneBy(['passwordResetToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Ce jeton de réinitialisation de mot de passe n\'est pas valide.');
        }

        $form = $this->createFormBuilder()
            ->add('password', PasswordType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->getData();

            $user->setPassword($password);
            $user->setPasswordResetToken(null);
            $this->doctrine->getManager()->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
