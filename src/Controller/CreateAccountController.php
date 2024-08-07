<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\User;
use App\Form\CreateAccountType;
use App\Service\PasswordGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class CreateAccountController extends AbstractController
{
    private PasswordGenerator $passwordGenerator;
    private MailerInterface $mailer;

    public function __construct(PasswordGenerator $passwordGenerator, MailerInterface $mailer)
    {
        $this->passwordGenerator = $passwordGenerator;
        $this->mailer = $mailer;
    }

    #[Route('/create/account/{id}', name: 'app_create_account')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $person = $user->getPerson();
        $personne = $entityManager->find(Person::class, $request->get('id'));
        $form = $this->createForm(CreateAccountType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $user->setCreatedAt(new \DateTimeImmutable())
                ->setCanLogin(1)
                ->setEmail($form->get('email')->getData())
                ->setRoles($personne->getRoles());

            $password = $this->passwordGenerator->generatePassword();
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            $personne->setUser($user);
            $email = (new TemplatedEmail())
                ->from(new Address('noreply@msconsulting-europe.com', 'MS_Consulting'))
                ->to($user->getEmail())
                ->subject('Bienvenue sur TMS')
                ->htmlTemplate('person/new.html.twig')
                ->context(['user' => $user, 'password' => $password]);
            try {
                $this->mailer->send($email);
                $this->addFlash('success', 'Email envoyé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email : '.$e->getMessage());
            }
            $this->addFlash('success', 'Création réussie !');

            $personne->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($personne);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('create_account/index.html.twig', [
            'controller_name' => 'CreateAccountController',
            'connectedPerson' => $person,
            'personne' => $personne,
            'form' => $form,
        ]);
    }
}
