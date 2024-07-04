<?php

namespace App\Controller\super_admin;

use App\Entity\Files;
use App\Entity\Person;
use App\Entity\User;
use App\Form\PersonType;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('super_admin/person', name: 'super_admin_app_person_')]
class PersonController extends AbstractController
{
    #[Route('/index', name: 'index', methods: ['GET'])]
    public function index(PersonRepository $personRepository): Response
    {
        return $this->render('super_admin/person/index.html.twig', [
            'people' => $personRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $person = new Person();
        $personForm = $this->createForm(PersonType::class, $person, [
            'context' => 'new',
        ]);
        $personForm->handleRequest($request);

        if ($personForm->isSubmitted() && $personForm->isValid()) {
            $entityManager->persist($person);
            $entityManager->flush();

            // Upload CV
            $cvFile = $personForm->get('cv')->getData();
            if ($cvFile) {
                $cvFilename = 'CV.'.$person->getFirstName().'-'.$person->getLastName().'.'.$cvFile->guessExtension();
                $cvHash = hash('sha256', $cvFilename);
                $cvHashFile = $cvHash.'.'.$cvFile->guessExtension();
                $cvFile->move($this->getParameter('kernel.project_dir').'/public/CV', $cvFilename);

                $file = new Files();
                $file->setLabel('CV')
                    ->setFile($cvHashFile)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setPerson($person)
                    ->setRealFileName($cvFilename);

                $entityManager->persist($file);
                $entityManager->flush();
            }

            // Upload Lettre de motivation
            $lmFile = $personForm->get('coverLetter')->getData();
            if ($lmFile) {
                $lmFilename = 'LM.'.$person->getFirstName().'-'.$person->getLastName().'.'.$lmFile->guessExtension();
                $lmHash = hash('sha256', $lmFilename);
                $lmHashFile = $lmHash.'.'.$lmFile->guessExtension();
                $lmFile->move($this->getParameter('kernel.project_dir').'/public/LM', $lmFilename);

                $file = new Files();
                $file->setLabel('LM')
                    ->setFile($lmHashFile)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setPerson($person)
                    ->setRealFileName($lmFilename);

                $entityManager->persist($file);
                $entityManager->flush();
            }

            // Upload convention de stage
            $csFile = $personForm->get('internshipAgreement')->getData();
            if ($csFile) {
                $csFilename = 'CS.'.$person->getFirstName().'-'.$person->getLastName().'.'.$csFile->guessExtension();
                $csHash = hash('sha256', $csFilename);
                $csHashFile = $csHash.'.'.$csFile->guessExtension();
                $csFile->move($this->getParameter('kernel.project_dir').'/public/CS', $csHashFile);

                $file = new Files();
                $file->setLabel('CS')
                    ->setFile($csHashFile)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setPerson($person)
                    ->setRealFileName($csFilename);

                $entityManager->persist($file);
                $entityManager->flush();
            }
            $entityManager->persist($person);
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/person/new.html.twig', [
            'person' => $person,
            'personForm' => $personForm,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Person $person): Response
    {
        return $this->render('super_admin/person/show.html.twig', [
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

            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/person/edit.html.twig', [
            'person' => $person,
            'personForm' => $personForm,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Person $person, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$person->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($person);
            $user = $entityManager->getRepository(User::class)->findOneBy(['person_id' => $person->getId()]);

            if ($user) {
                $entityManager->remove($user);
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
    }
}
