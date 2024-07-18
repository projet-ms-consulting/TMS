<?php

namespace App\Controller\super_admin;

use App\Entity\Person;
use App\Entity\User;
use App\Form\PersonType;
use App\Form\TraineeRoleType;
use App\Form\TraineeType;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('super_admin/trainee', name: 'super_admin_app_trainee_')]
class TraineeController extends AbstractController
{
    #[Route('/index', name: 'index', methods: ['GET'])]
    public function index(PersonRepository $personRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 8);
        $sort = $request->query->get('sort', 'a.id');
        $direction = $request->query->get('direction', 'asc');
        $person = $personRepository->paginateTrainee($page, $limit);
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/trainee/index.html.twig', [
            'personne' => $person,
            'person' => $personne,
        ]);
    }

    #[Route('/new/', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PersonRepository $personRepository): Response
    {
        $idPerson = intval($request->query->get('id'));

        $person = $personRepository->find($idPerson);

        $traineeForm = $this->createForm(TraineeRoleType::class, $person);
        $traineeForm->handleRequest($request);

        if ($traineeForm->isSubmitted() && $traineeForm->isValid()) {
            $entityManager->persist($person);
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);

        }

        return $this->render('super_admin/trainee/new.html.twig', [
            'person' => $person,
            'form' => $traineeForm,
        ]);
    }


    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Person $person): Response
    {
        return $this->render('super_admin/trainee/show.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $person, EntityManagerInterface $entityManager): Response
    {
        $personForm = $this->createForm(PersonType::class, $person, [
            'context' => 'edit',
        ]);
        $personForm->handleRequest($request);

        if ($personForm->isSubmitted() && $personForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_app_trainee_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/trainee/edit.html.twig', [
            'person' => $person,
            'personForm' => $personForm,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Person $person, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$person->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($person);
            $user = $person->getUser();
            if ($user) {
                $entityManager->remove($user);
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('super_admin_app_trainee_index', [], Response::HTTP_SEE_OTHER);
    }
}
