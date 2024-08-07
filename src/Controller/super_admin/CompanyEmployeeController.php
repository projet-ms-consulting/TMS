<?php

namespace App\Controller\super_admin;

use App\Entity\Person;
use App\Entity\User;
use App\Form\CompanyEmployeeType;
use App\Form\PersonType;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('super_admin/company_employee', name: 'super_admin_app_company_employee_')]
class CompanyEmployeeController extends AbstractController
{
    #[Route('/index', name: 'index', methods: ['GET'])]
    public function index(PersonRepository $personRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 8);
        $sort = $request->query->get('sort', 'a.id');
        $direction = $request->query->get('direction', 'asc');
        $person = $personRepository->paginateCompanyEmployee($page, $limit);
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/company_employee/index.html.twig', [
            'personne' => $person,
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Person $person, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/company_employee/show.html.twig', [
            'personne' => $person,
            'user' => $person->getUser(),
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $person, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();

        $form = $this->createForm(CompanyEmployeeType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $person->setCompanyReferent(null)
                    ->setManager(null)
                    ->setInternshipSupervisor(null)
                    ->setUpdatedAt(new \DateTimeImmutable())
                    ->setRoles([$form->get('roles')->getData()]);
            if ($person->getRoles()[0] == "ROLE_COMPANY_REFERENT") {
                $person->setCompanyReferent($person);
            }
            if ($person->getRoles()[0] == "ROLE_COMPANY_INTERNSHIP") {
                $person->setInternshipSupervisor($person);
            }
            if ($person->getRoles()[0] == "ROLE_ADMIN") {
                $person->setManager($person);
            }

            $person->getUser()?->setRoles([$form->get('roles')->getData()]);
            $person->getUser()?->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'Modification effectuée!');

            return $this->redirectToRoute('super_admin_app_company_employee_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/company_employee/edit.html.twig', [
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

        return $this->redirectToRoute('super_admin_app_company_employee_index', [], Response::HTTP_SEE_OTHER);
    }
}
