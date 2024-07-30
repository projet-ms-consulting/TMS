<?php

namespace App\Controller\super_admin;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/project', name: 'super_admin_project_')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();
        $projects = $projectRepository->findAll();


        return $this->render('super_admin/project/index.html.twig', [
            'projects' => $projects,
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        $user = $this->getUser();
        $personne = $user->getPerson();
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/project/new.html.twig', [
            'project' => $project,
            'form' => $form,
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/project/show.html.twig', [
            'project' => $project,
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($project->getPerson());
            $entityManager->remove($project->getLinks());
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('super_admin_project_index', [], Response::HTTP_SEE_OTHER);
    }
}
