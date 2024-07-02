<?php

namespace App\Controller\admin;

use App\Entity\Files;
use App\Entity\Person;
use App\Form\PersonType;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/person')]
class PersonController extends AbstractController
{
    #[Route('/', name: 'app_person_index', methods: ['GET'])]
    public function index(PersonRepository $personRepository): Response
    {
        return $this->render('person/index.html.twig', [
            'people' => $personRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_person_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $person = new Person();
        $personForm = $this->createForm(PersonType::class, $person);
        $personForm->handleRequest($request);

        if ($personForm->isSubmitted() && $personForm->isValid()) {

            // Upload CV
            $cvFile = $personForm->get('cv')->getData();
            if ($cvFile) {
                $cvFilename ='CV.' . $person->getFirstName().'-'.$person->getLastName() . '.'.$cvFile->guessExtension();
                $cvHash = hash('sha256', $cvFilename);
                $cvHashFile =$cvHash . '.' . $cvFile->guessExtension();
                $cvFile->move($this->getParameter('kernel.project_dir') . '/public/CV', $cvFilename );

                $file = new Files();
                $file->setLabel('CV')->setFile($cvHashFile)->setCreatedAt(new \DateTimeImmutable())->setPerson($person);

                $entityManager->persist($file);
                $entityManager->persist($file->getPerson());
                $entityManager->flush();
            }

            // Upload Lettre de motivation
            $lmFile = $personForm->get('coverLetter')->getData();
            if ($lmFile) {
                $lmFilename ='LM.' . $person->getFirstName().'-'.$person->getLastName() . '.'.$lmFile->guessExtension();
                $lmHash = hash('sha256', $lmFilename);
                $lmHashFile =$lmHash . '.' . $lmFile->guessExtension();
                $lmFile->move($this->getParameter('kernel.project_dir') . '/public/LM', $lmFilename );

                $file = new Files();
                $file->setLabel('LM')->setFile($lmHashFile)->setCreatedAt(new \DateTimeImmutable())->setPerson($person);

                $entityManager->persist($file);
                $entityManager->persist($file->getPerson());
                $entityManager->flush();
            }

            // Upload convention de stage
            $csFile = $personForm->get('internshipAgreement')->getData();
            if ($csFile) {
                $csFilename ='CS.' . $person->getFirstName().'-'.$person->getLastName() . '.'.$csFile->guessExtension();
                $csHash = hash('sha256', $csFilename);
                $csHashFile =$csHash . '.' . $csFile->guessExtension();
                $csFile->move($this->getParameter('kernel.project_dir') . '/public/CS', $csHashFile );

                $file = new Files();
                $file->setLabel('CS')->setFile($csHashFile)->setCreatedAt(new \DateTimeImmutable())->setPerson($person);

                $entityManager->persist($file);
                $entityManager->persist($file->getPerson());
                $entityManager->flush();
            }

            $entityManager->persist($person);
            $entityManager->flush();


            return $this->redirectToRoute('app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('person/new.html.twig', [
            'person' => $person,
            'personForm' => $personForm,
        ]);
    }

    #[Route('/{id}', name: 'app_person_show', methods: ['GET'])]
    public function show(Person $person): Response
    {
        return $this->render('person/show.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_person_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $person, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('person/edit.html.twig', [
            'person' => $person,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_person_delete', methods: ['POST'])]
    public function delete(Request $request, Person $person, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$person->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($person);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_person_index', [], Response::HTTP_SEE_OTHER);
    }
}
