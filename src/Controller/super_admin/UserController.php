<?php

namespace App\Controller\super_admin;

use App\Entity\Files;
use App\Entity\User;
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

        $personId = $request->query->get('id');
        $person = $user->getPerson();
        if ($personId) {
            $person = $personRepository->find($personId);
        }
        $user->setPerson($person);

        $userForm = $this->createForm(UserType::class, $user, [
            'selected_person' => $person,
        ]);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user->setPassword(password_hash($userForm->get('password')->getData(), PASSWORD_BCRYPT));
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setEmail($userForm->get('email')->getData());

            $roles = $userForm->get('roles')->getData();
            if (is_string($roles)) {
                $roles = [$roles];
            }
            $user->setRoles($roles);

            // *************  Upload CV ***************
            $cvFile = $userForm->get('cv')->getData();
            if ($cvFile) {
                $cvFilename = 'CV.'.$person->getFirstName().'-'.$person->getLastName().'.'.$cvFile->guessExtension();
                $cvHash = hash('sha256', $cvFilename);
                $cvHashFile = $cvHash.'.'.$cvFile->guessExtension();
                $cvFile->move($this->getParameter('kernel.project_dir').'public/doc/', $cvFilename);

                $file = new Files();
                $file->setLabel('CV')
                    ->setFile($cvHashFile)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setPerson($person)
                    ->setRealFileName($cvFilename);

                $entityManager->persist($file);
                $entityManager->flush();
            }

            $entityManager->persist($user);
            $entityManager->persist($person);
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/user/new.html.twig', [
            'person' => $person,
            'form' => $userForm->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();
        return $this->render('super_admin/user/show.html.twig', [
            'user' => $user,
            'person' => $personne,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);
        $user = $this->getUser();
        $personne = $user->getPerson();

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/user/edit.html.twig', [
            'user' => $user,
            'person' => $personne,
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
