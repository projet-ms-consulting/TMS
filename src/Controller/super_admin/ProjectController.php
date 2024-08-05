<?php

namespace App\Controller\super_admin;

use App\Entity\Links;
use App\Entity\Project;
use App\Form\ProjectLinksType;
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
            $project->setCompany($form->get('company')->getData());
            $project->setName($form->get('name')->getData());
            $project->setDescription($form->get('description')->getData());

            if ($form->has('company')) {
                $persons = $form->get('participant')->getData();
                foreach ($persons as $person) {
                    $project->addParticipant($person);
                }
            }
            if (null != $form->get('linkGit')->getData()) {
                $link = new Links();
                $link->setLabel('Github');
                $link->setLink($form->get('linkGit')->getData());
                $link->setProject($project);
                $entityManager->persist($link);
            }

            $project->setCreatedAt(new \DateTimeImmutable());
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
        $links = $project->getLinks();
        $participants = $project->getParticipant();

        foreach ($participants as $participant) {
            $participant->getFullName();
        }
        foreach ($links as $link) {
            $link->getLabel();
            $link->getLink();
        }

        return $this->render('super_admin/project/show.html.twig', [
            'project' => $project,
            'connectedPerson' => $personne,
            'links' => $links,
            'participants' => $participants,
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
            $entityManager->remove($project->getParticipant());
            $entityManager->remove($project->getLinks());
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('super_admin_project_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/newlink/{id}', name: 'link', methods: ['GET', 'POST'])]
    public function link(Request $request, EntityManagerInterface $entityManager, Project $project): Response
    {
        $form = $this->createForm(ProjectLinksType::class);
        $form->handleRequest($request);

        $user = $this->getUser();
        $personne = $user->getPerson();
        if ($form->isSubmitted() && $form->isValid()) {
            if ('Github' === $form->get('labelChoice')->getData()) {
                $link = new Links();
                $link->setLabel('Github');
                $link->setLink($form->get('linkGit')->getData());
                $link->setProject($project);
                $entityManager->persist($link);
            }
            if ('Trello' === $form->get('labelChoice')->getData()) {
                $link = new Links();
                $link->setLabel('Trello');
                $link->setLink($form->get('linkTrello')->getData());
                $link->setProject($project);
                $entityManager->persist($link);
            }
            if ('Autre' === $form->get('labelChoice')->getData()) {
                $link = new Links();
                $link->setLabel('Autre');
                $link->setLink($form->get('linkOther')->getData());
                $link->setProject($project);
                $entityManager->persist($link);
            }

            $project->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/project/newLink.html.twig', [
            'project' => $project,
            'form' => $form,
            'connectedPerson' => $personne,
        ]);
    }
}
