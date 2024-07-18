<?php

namespace App\Controller;

use App\Entity\Files;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    private const FILES_DIRECTORY = __DIR__.'/../../public/assets/FilesDirectory';

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
//            $profilePictureFile = $form->get('profilePicture')->getData();


            if ($firstName) {
                $person->setFirstName($firstName);
            }

            if ($lastName) {
                $person->setLastName($lastName);
            }

            if ($cvFile) {
                $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$cvFile->guessExtension();

                try {
                    if (!in_array($cvFile->guessExtension(), ['pdf', 'jpg'])) {
                        throw new \UnexpectedValueException('Format de fichier non valide. Veuillez télécharger un fichier PDF ou JPG.');
                    }

                    $cvFile->move(
                        self::FILES_DIRECTORY,
                        $newFilename
                    );

                    $cv = new Files();
                    $cv->setLabel($cvType);
                    $cv->setFile($newFilename);
                    $cv->setRealFileName($originalFilename);
                    $cv->setCreatedAt(new \DateTimeImmutable());
                    $cv->setPerson($person);

                    $entityManager->persist($cv);
                    $entityManager->flush();
                } catch (FileException|\UnexpectedValueException $e) {
                    $this->addFlash('error', $e->getMessage());

                    return $this->redirectToRoute('app_profil_edit');
                }
            }
//
//            if ($profilePictureFile) {
//                $originalFilename = pathinfo($profilePictureFile->getClientOriginalName(), PATHINFO_FILENAME);
//                $newFilename = $originalFilename . '-' . uniqid() . '.' . $profilePictureFile->guessExtension();
//
//                try {
//                    $profilePictureFile->move(
//                        self::FILES_DIRECTORY,
//                        $newFilename
//                    );
//
//                    $person->setProfilePicture($newFilename);
//                } catch (FileException $e) {
//                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo de profil.');
//                    return $this->redirectToRoute('app_profil_edit');
//                }
//            }

            $newPassword = $form->get('password')->getData();
            if ($newPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
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
    public function deleteCV(int $id, EntityManagerInterface $entityManager): Response
    {
        $cv = $entityManager->getRepository(Files::class)->find($id);

        if (!$cv) {
            throw $this->createNotFoundException('Le CV avec l\'id '.$id.' n\'existe pas.');
        }

        $entityManager->remove($cv);
        $entityManager->flush();

        $this->addFlash('success', 'CV supprimé avec succès.');

        return $this->redirectToRoute('app_profil');
    }
}
