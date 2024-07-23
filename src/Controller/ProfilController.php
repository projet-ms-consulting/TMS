<?php

namespace App\Controller;

use App\Entity\Files;
use App\Form\ProfilType;
use App\Repository\FilesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
            'person' => $person,
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
            $cvFile = $form->get('cv')->getData();
            $cvType = $form->get('cvType')->getData();

            if ($firstName) {
                $person->setFirstName($firstName);
            }

            if ($lastName) {
                $person->setLastName($lastName);
            }

            if ($cvFile) {
                $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.md5(uniqid()).'.'.$cvFile->guessExtension();

                try {
                    $cvFile->move(
                        $this->getParameter('kernel.project_dir').'/files/'.$person->getId(),
                        $newFilename
                    );

                    $cv = new Files();
                    $cv->setLabel($cvType);
                    $cv->setFile($newFilename);
                    $cv->setRealFileName($cvFile->getClientOriginalName());
                    $cv->setCreatedAt(new \DateTimeImmutable());
                    $cv->setPerson($person);

                    $entityManager->persist($cv);
                } catch (FileException|\UnexpectedValueException $e) {
                    $this->addFlash('error', $e->getMessage());

                    return $this->redirectToRoute('app_profil_edit');
                }
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
            'form' => $form->createView(),
            'person' => $person,
        ]);
    }

    #[Route('/profil/delete-cv/{id}', name: 'app_delete_cv')]
    public function deleteCV(int $id, FilesRepository $filesRepository, EntityManagerInterface $entityManager): Response
    {
        $cv = $filesRepository->find($id);

        if (!$cv) {
            throw $this->createNotFoundException('Le CV avec l\'id '.$id.' n\'existe pas.');
        }

        $filePath = $this->getParameter('kernel.project_dir').'/files/'.$cv->getPerson()->getId().'/'.$cv->getFile();

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

        $filePath = $this->getParameter('kernel.project_dir').'/files/'.$file->getPerson()->getId().'/'.$file->getFile();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier demandé n\'existe pas.');
        }

        $mimeType = mime_content_type($filePath);

        return $this->file($filePath, $file->getFile(), ResponseHeaderBag::DISPOSITION_INLINE, ['Content-Type' => $mimeType]);
    }
}
