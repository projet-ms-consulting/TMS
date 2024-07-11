<?php

namespace App\Controller\super_admin;

use App\Entity\Person;
use App\Entity\User;
use App\Form\PersonType;
use App\Form\UserType;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('super_admin/user', name: 'super_admin_app_user_')]
class UserController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('super_admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PersonRepository $personRepository): Response
    {
        $user = new User();
        $user->setPerson(new Person());

        $personId = $request->query->get('id');
        $person = $entityManager->getRepository(Person::class)->find($personId);

        if ($person) {
        $user->setPerson($person);
    } else {
            $user->setPerson(new Person());
    }

        $userForm = $this->createForm(UserType::class, $user, [
            'selected_person' => $person,
        ]);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user->setPassword(password_hash($userForm->getData()->getPassword(), PASSWORD_DEFAULT));
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setRoles($userForm->getData()->getRoles());
            $user->setEverLoggedIn(false);
            $person->setUser($user);

            $entityManager->persist($user);
            $entityManager->persist($person);
            $entityManager->flush();

            if (in_array('ROLE_TRAINEE', $userForm->getData()->getRoles())) {
                return $this->redirectToRoute('super_admin_app_trainee_new', ['id' => $user->getPerson()->getId()]);
            }
            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
        }

            return $this->render('super_admin/user/new.html.twig', [
            'userForm' => $userForm->createView(),
            'person' => $user->getPerson(),
            'people' => $personRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('super_admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/user/edit.html.twig', [
            'user' => $user,
//            'person' => $user->getPerson(),
            'userForm' => $userForm,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }
        $this->addFlash('success', 'L\'utilisateur a bien été supprimé');

        return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
    }
}
