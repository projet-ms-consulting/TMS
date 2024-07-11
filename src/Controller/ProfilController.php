<?php
namespace App\Controller;

use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/profil/edit', name: 'app_profil_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $person = $user->getPerson();

        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $firstName = $form->get('firstName')->getData();
            $lastName = $form->get('lastName')->getData();

            if ($firstName) {
                $person->setFirstName($firstName);
            }

            if ($lastName) {
                $person->setLastName($lastName);
            }

            $entityManager->persist($person);
            $entityManager->flush();

            return $this->redirectToRoute('app_profil');
        }

        $form->get('firstName')->setData($person->getFirstName());
        $form->get('lastName')->setData($person->getLastName());

        return $this->render('profil/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}