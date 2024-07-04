<?php

namespace App\Controller\super_admin;

use App\Entity\School;
use App\Form\SchoolType;
use App\Repository\PersonRepository;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'super_admin_school_')]
class SchoolController extends AbstractController
{
    #[Route('super_admin/school/index', name: 'index', methods: ['GET'])]
    public function index(SchoolRepository $schoolRepository, Request $request, PersonRepository $personRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 8);
        $sort = $request->query->get('sort', 's.id');
        $direction = $request->query->get('direction', 'asc');
        $schools = $schoolRepository->paginateSchools($page, $limit);
        $persons = $personRepository->findAll();

        return $this->render('super_admin/school/index.html.twig', [
            'schools' => $schools,
            'page' => $page,
            'limit' => $limit,
            'sort' => $sort,
            'direction' => $direction,
            'persons' => $persons,
        ]);
    }

    #[Route('super_admin/school/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $school = new School();
        $form = $this->createForm(SchoolType::class, $school);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $school->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($school);
            $entityManager->flush();

            $this->addFlash('success', 'Ecole '.$school->getName().' créé avec succès!');
            return $this->redirectToRoute('super_admin_school_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/school/new.html.twig', [
            'school' => $school,
            'form' => $form,
        ]);
    }

    #[Route('super_admin/school/show/{id}', name: 'show', methods: ['GET'])]
    public function show(School $school): Response
    {
        return $this->render('super_admin/school/show.html.twig', [
            'school' => $school,
        ]);
    }

    #[Route('super_admin/school/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, School $school, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SchoolType::class, $school);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $school->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Ecole '.$school->getName().' modifié avec succes!');

            return $this->redirectToRoute('super_admin_school_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/school/edit.html.twig', [
            'school' => $school,
            'form' => $form,
        ]);
    }

    #[Route('/super_admin/school/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, School $school, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$school->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($school);
            $entityManager->flush();
        }
        $this->addFlash('success', 'Ecole '.$school->getName().' supprimé avec succes!');

        return $this->redirectToRoute('super_admin_school_index', [], Response::HTTP_SEE_OTHER);
    }
}
