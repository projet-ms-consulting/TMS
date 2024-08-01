<?php

namespace App\Controller\super_admin;

use App\Entity\Person;
use App\Form\PersonType;
use App\Form\SchoolEmployeeType;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('super_admin/school_employee', name: 'super_admin_app_school_employee_')]
class SchoolEmployeeController extends AbstractController
{
    #[Route('/index', name: 'index', methods: ['GET'])]
    public function index(PersonRepository $personRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 8);
        $sort = $request->query->get('sort', 'a.id');
        $direction = $request->query->get('direction', 'asc');
        $person = $personRepository->paginateSchoolEmployee($page, $limit);
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/school_employee/index.html.twig', [
            'personne' => $person,
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Person $person): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/school_employee/show.html.twig', [
            'personne' => $person,
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $person, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();
        $form = $this->createForm(SchoolEmployeeType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $person->setUpdatedAt(new \DateTimeImmutable())
                    ->setRoles([$form->get('roles')->getData()])
                    ->setUser($person->getUser());

            $entityManager->flush();

            $this->addFlash('success', 'Modification effectuée!');

            return $this->redirectToRoute('super_admin_app_school_employee_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/school_employee/edit.html.twig', [
            'personne' => $person,
            'form' => $form,
            'connectedPerson' => $personne,
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
        $this->addFlash('success', 'Suppression réussie !');

        return $this->redirectToRoute('super_admin_app_school_employee_index', [], Response::HTTP_SEE_OTHER);
    }
}
