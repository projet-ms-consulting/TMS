<?php

namespace App\Controller\super_admin;

use App\Entity\Person;
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
    public function index(PersonRepository $personRepository, Person $person, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $filteredPersons = $personRepository->filterCompanyEmployeePersons();

        return $this->render('super_admin/company_employee/index.html.twig', [
            'persons' => $filteredPersons,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Person $person, UserRepository $userRepository): Response
    {
        return $this->render('super_admin/company_employee/show.html.twig', [
            'person' => $person,
            'user' => $person->getUser(),
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

            return $this->redirectToRoute('super_admin_app_company_employee_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/company_employee/edit.html.twig', [
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

        return $this->redirectToRoute('super_admin_app_company_employee_index', [], Response::HTTP_SEE_OTHER);
    }
}
