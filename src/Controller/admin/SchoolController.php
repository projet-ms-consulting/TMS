<?php

namespace App\Controller\admin;

use App\Entity\School;
use App\Form\SchoolType;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/school', name: 'admin_school_')]
class SchoolController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(SchoolRepository $schoolRepository): Response
    {
        $schools = $schoolRepository->findAll();
        return $this->render('admin/school/index.html.twig', [
            'schools' => $schools,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $school = new School();
        $form = $this->createForm(SchoolType::class, $school);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $school->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($school);
            $entityManager->flush();

            $this->addFlash('success', 'Ecole '.$school->getName().' crée avec succes!');
            return $this->redirectToRoute('admin_school_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/school/new.html.twig', [
            'school' => $school,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(School $school): Response
    {
        return $this->render('admin/school/show.html.twig', [
            'school' => $school,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, School $school, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SchoolType::class, $school);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $school->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Ecole '.$school->getName().' modifié avec succes!');
            return $this->redirectToRoute('admin_school_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/school/edit.html.twig', [
            'school' => $school,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, School $school, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$school->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($school);
            $entityManager->flush();
        }
        $this->addFlash('success', 'Ecole '.$school->getName().' supprimé avec succes!');
        return $this->redirectToRoute('admin_school_index', [], Response::HTTP_SEE_OTHER);
    }
}
