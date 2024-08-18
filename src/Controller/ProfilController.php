<?php

namespace App\Controller;

use App\Entity\Files;
use App\Entity\Person;
use App\Form\ProfilType;
use App\Repository\FilesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(): Response
    {
        $user = $this->getUser();
        $person = $user->getPerson();

        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'connectedPerson' => $person,
        ]);
    }

    #[Route('/profil/edit', name: 'app_profil_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $person = $user->getPerson();

        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $firstName = $form->get('firstName')->getData();
            $lastName = $form->get('lastName')->getData();

            if ($firstName) {
                $person->setFirstName($firstName);
            }

            if ($lastName) {
                $person->setLastName($lastName);
            }

            if ($form->has('cv')) {
                $label = 'CV';
                $file = $this->deleteFile($label, $person, $entityManager);
                $fileGiven = $form->get('cv')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $file = $this->newFile($label, $person, $file, $fileGiven);
                    $entityManager->persist($file);
                }
            }
            if ($form->has('lm')) {
                $label = 'LM';
                $file = $this->deleteFile($label, $person, $entityManager);
                $fileGiven = $form->get('lm')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $file = $this->newFile($label, $person, $file, $fileGiven);
                    $entityManager->persist($file);
                }
            }
            if ($form->has('cs')) {
                $label = 'CS';
                $file = $this->deleteFile($label, $person, $entityManager);
                $fileGiven = $form->get('cs')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $file = $this->newFile($label, $person, $file, $fileGiven);
                    $entityManager->persist($file);
                }
            }
            if ($form->has('rs')) {
                $label = 'RS';
                $this->deleteFile($label, $person, $entityManager);
                $fileGiven = $form->get('rs')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $file = $this->newFile($label, $person, $file, $fileGiven);
                    $entityManager->persist($file);
                }
            }
            if ($form->has('other')) {
                $label = 'Autre';
                $file = $this->deleteFile($label, $person, $entityManager);
                $fileGiven = $form->get('other')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $file = $this->newFile($label, $person, $file, $fileGiven);
                    $entityManager->persist($file);
                }
            }
            $newMail = $form->get('email')->getData();
            if($newMail) {
                $user->setEmail($newMail);
                $person->setMailContact($newMail);
            }
            $newPassword = $form->get('password')->getData();
            if ($newPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $user->setEverLoggedIn(true);
            }

            $entityManager->persist($person);
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/edit.html.twig', [
            'form' => $form,
            'connectedPerson' => $person,
            'user' => $user,
        ]);
    }

    #[Route('/profil/delete-cv/{id}', name: 'app_delete_cv')]
    public function deleteCV(int $id, FilesRepository $filesRepository, EntityManagerInterface $entityManager): Response
    {
        $cv = $filesRepository->find($id);

        if (!$cv) {
            throw $this->createNotFoundException('Le CV avec l\'id '.$id.' n\'existe pas.');
        }

        $filePath = $this->getParameter('kernel.project_dir').'/files/'.$cv->getPerson()->getId().'/'.$cv->getRealFileName();

        if (file_exists($filePath)) {
            unlink($filePath);
        } else {
            $this->addFlash('error', 'Le fichier n\'existe pas ou a déjà été supprimé.');
        }

        $entityManager->remove($cv);
        $entityManager->flush();

        $this->addFlash('success', 'CV supprimé avec succès.');

        return $this->redirectToRoute('app_profil');
    }

    #[Route('/profil/file/{id}/{name}', name: 'app_file_show', methods: ['GET'])]
    public function showFile(int $id, string $name, FilesRepository $filesRepository): Response
    {
        $file = $filesRepository->find($id);

        if (!$file || $file->getRealFileName() !== $name) {
            throw $this->createNotFoundException('Fichier non trouvé.');
        }

        $filePath = $this->getParameter('kernel.project_dir').'/files/'.$file->getPerson()->getId().'/'.$file->getRealFileName();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier demandé n\'existe pas.');
        }

        $mimeType = mime_content_type($filePath);

        return $this->file($filePath, $file->getFile(), ResponseHeaderBag::DISPOSITION_INLINE, ['Content-Type' => $mimeType]);
    }

    public function newFile(string $label, Person $person, Files $file, mixed $fileGiven): Files
    {
        $fileName = $label.$person->getFirstName().'-'.$person->getLastName().'.'.$fileGiven->guessExtension();
        $fileHash = hash('sha256', $fileName);
        $file->setFile($fileHash)
            ->setLabel($label)
            ->setPerson($person)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setRealFileName($fileName);
        $fileGiven->move(
            $this->getParameter('kernel.project_dir').'/files/'.$person->getId().'/', $fileName
        );

        return $file;
    }

    public function deleteFile(string $label, Person $person, EntityManagerInterface $entityManager)
    {
        $oldFiles = $person->getFiles();
        foreach ($oldFiles as $file) {
            if ($label === $file->getLabel()) {
                $filePath = ($this->getParameter('kernel.project_dir').'/files/'.$person->getId().'/'.$file->getFile());
                if (file_exists($file->getFile())) {
                    unlink($filePath);

                }
                $entityManager->remove($file);
            }
        }

        return null;
    }
}
