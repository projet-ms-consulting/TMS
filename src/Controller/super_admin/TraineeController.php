<?php

namespace App\Controller\super_admin;

use App\Entity\Files;
use App\Entity\Person;
use App\Form\TraineeType;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('super_admin/trainee', name: 'super_admin_app_trainee_')]
class TraineeController extends AbstractController
{
    #[Route('/index', name: 'index', methods: ['GET'])]
    public function index(PersonRepository $personRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 8);
        $sort = $request->query->get('sort', 'a.id');
        $direction = $request->query->get('direction', 'asc');
        $person = $personRepository->paginateTrainee($page, $limit);
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/trainee/index.html.twig', [
            'personne' => $person,
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Person $person): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/trainee/show.html.twig', [
            'personne' => $person,
            'connectedPerson' => $personne,
        ]);
    }


    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $person, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();

        $traineeForm = $this->createForm(TraineeType::class, $person);
        $traineeForm->handleRequest($request);

        if ($traineeForm->isSubmitted() && $traineeForm->isValid()) {
            $person->setSchoolSupervisor(null)
                    ->setCompanyReferent(null)
                    ->setManager(null)
                    ->setInternshipSupervisor(null);

            $person->setUpdatedAt(new \DateTimeImmutable())
                    ->setStartInternship($traineeForm->getData()->getStartInternship())
                    ->setEndInternship($traineeForm->getData()->getEndInternship())
                    ->setCompany($traineeForm->getData()->getCompany())
                    ->setSchool($traineeForm->getData()->getSchool())
                    ->setCompanyReferent($traineeForm->get('companyReferent')->getData())
                    ->setInternshipSupervisor($traineeForm->get('internshipSupervisor')->getData())
                    ->setSchoolSupervisor($traineeForm->get('schoolSupervisor')->getData())
                    ->setManager($traineeForm->get('manager')->getData());

            // CV
            if ($traineeForm->has('cv')) {
                $cvFile = $traineeForm->get('cv')->getData();
                if ($cvFile) {
                    // Récupérer l'ancien CV s'il existe
                    $existingCv = null;
                    foreach ($person->getFiles() as $oldFile) {
                        if ('CV' === $oldFile->getLabel()) {
                            $existingCv = $oldFile;
                            break;
                        }
                    }

                    if ($existingCv) {
                        // Supprimer le cv existant
                        $filePath = $this->getParameter('kernel.project_dir').'/files/'.$existingCv->getFile();
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }

                        // Update cv
                        $cvFilename = 'CV.'.$person->getFirstName().'-'.$person->getLastName().'.'.$cvFile->guessExtension();
                        $cvHash = hash('sha256', $cvFilename);
                        $cvHashFile = $cvHash.'.'.$cvFile->guessExtension();
                        $cvFile->move($this->getParameter('kernel.project_dir').'/files/', $cvFilename);

                        $existingCv->setFile($cvHashFile)
                            ->setUpdatedAt(new \DateTimeImmutable())
                            ->setRealFileName($cvFilename);
                    } else {
                        // Ajouter CV s'il n'existe pas
                        $cvFilename = 'CV.'.$person->getFirstName().'-'.$person->getLastName().'.'.$cvFile->guessExtension();
                        $cvHash = hash('sha256', $cvFilename);
                        $cvHashFile = $cvHash.'.'.$cvFile->guessExtension();
                        $cvFile->move($this->getParameter('kernel.project_dir').'/files/', $cvFilename);

                        $file = new Files();
                        $file->setLabel('CV')
                            ->setFile($cvHashFile)
                            ->setCreatedAt(new \DateTimeImmutable())
                            ->setUpdatedAt(new \DateTimeImmutable())
                            ->setPerson($person)
                            ->setRealFileName($cvFilename);

                        $entityManager->persist($file);
                    }
                }
            }
            // LETTRE DE MOTIVATION
            if ($traineeForm->has('coverLetter')) {
                $lmFile = $traineeForm->get('coverLetter')->getData();
                if ($lmFile) {
                    // Récupérer l'ancien CV s'il existe
                    $existingLm = null;
                    foreach ($person->getFiles() as $oldFile) {
                        if ('LM' === $oldFile->getLabel()) {
                            $existingCv = $oldFile;
                            break;
                        }
                    }

                    if ($existingLm) {
                        // Supprimer la lettre de motivation existante
                        $filePath = $this->getParameter('kernel.project_dir').'/files/'.$existingLm->getFile();
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }

                        // Update LM
                        $lmFilename = 'LM.'.$person->getFirstName().'-'.$person->getLastName().'.'.$lmFile->guessExtension();
                        $lmHash = hash('sha256', $lmFilename);
                        $lmHashFile = $lmHash.'.'.$lmFile->guessExtension();
                        $lmFile->move($this->getParameter('kernel.project_dir').'/files/', $lmHashFile);

                        $existingLm->setFile($lmHashFile)
                            ->setUpdatedAt(new \DateTimeImmutable())
                            ->setRealFileName($lmFilename);
                    } else {
                        // Ajouter LM si elle n'existe pas
                        $lmFilename = 'LM.'.$person->getFirstName().'-'.$person->getLastName().'.'.$lmFile->guessExtension();
                        $lmHash = hash('sha256', $lmFilename);
                        $lmHashFile = $lmHash.'.'.$lmFile->guessExtension();
                        $lmFile->move($this->getParameter('kernel.project_dir').'/files/', $lmHashFile);

                        $file = new Files();
                        $file->setLabel('LM')
                            ->setFile($lmHashFile)
                            ->setCreatedAt(new \DateTimeImmutable())
                            ->setUpdatedAt(new \DateTimeImmutable())
                            ->setPerson($person)
                            ->setRealFileName($lmFilename);

                        $entityManager->persist($file);
                    }
                }
            }
            // CONVENTION DE STAGE
            if ($traineeForm->has('internshipAgreement')) {
                $csFile = $traineeForm->get('internshipAgreement')->getData();
                if ($csFile) {
                    // Récupérer l'ancienne CS si elle existe
                    $existingCs = null;
                    foreach ($person->getFiles() as $oldFile) {
                        if ('CS' === $oldFile->getLabel()) {
                            $existingCs = $oldFile;
                            break;
                        }
                    }

                    if ($existingCs) {
                        // Supprimer la CS existante
                        $filePath = $this->getParameter('kernel.project_dir').'/files/'.$existingCs->getFile();
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }

                        // Update CS
                        $csFilename = 'CS.'.$person->getFirstName().'-'.$person->getLastName().'.'.$csFile->guessExtension();
                        $csHash = hash('sha256', $csFilename);
                        $csHashFile = $csHash.'.'.$csFile->guessExtension();
                        $csFile->move($this->getParameter('kernel.project_dir').'/files/', $csHashFile);

                        $existingCs->setFile($csHashFile)
                            ->setUpdatedAt(new \DateTimeImmutable())
                            ->setRealFileName($csFilename);
                    } else {
                        // Ajouter CS si elle n'existe pas
                        $csFilename = 'CS.'.$person->getFirstName().'-'.$person->getLastName().'.'.$csFile->guessExtension();
                        $csHash = hash('sha256', $csFilename);
                        $csHashFile = $csHash.'.'.$csFile->guessExtension();
                        $csFile->move($this->getParameter('kernel.project_dir').'/files/', $csHashFile);

                        $file = new Files();
                        $file->setLabel('CS')
                            ->setFile($csHashFile)
                            ->setCreatedAt(new \DateTimeImmutable())
                            ->setUpdatedAt(new \DateTimeImmutable())
                            ->setPerson($person)
                            ->setRealFileName($csFilename);

                        $entityManager->persist($file);
                    }
                }
            }
            $entityManager->flush();

            $this->addFlash('success', 'Modification réussie !');

            return $this->redirectToRoute('super_admin_app_trainee_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/trainee/edit.html.twig', [
            'personne' => $person,
            'traineeForm' => $traineeForm,
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/delete/file/{id}', name: 'delete_file', methods: ['POST'])]
    public function deleteFile(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $file = $entityManager->getRepository(Files::class)->find($id);

        if ($this->isCsrfTokenValid('delete'.$file->getId(), $request->request->get('_token'))) {
            if ('CV' == $file->getLabel() || 'LM' == $file->getLabel() || 'CS' == $file->getLabel()) {
                $filePath = $this->getParameter('kernel.project_dir').'/files/'.$file->getFile();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $entityManager->remove($file);
                $entityManager->flush();
            }

            $this->addFlash('success', 'Suppression réussie !');

            return $this->redirectToRoute('super_admin_app_trainee_show', ['id' => $file->getPerson()->getId()], Response::HTTP_SEE_OTHER);
        }

        throw $this->createNotFoundException('Token CSRF invalide.');
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

        $this->addFlash('success', 'Suppression réussie !');

        return $this->redirectToRoute('super_admin_app_trainee_index', [], Response::HTTP_SEE_OTHER);
    }
}