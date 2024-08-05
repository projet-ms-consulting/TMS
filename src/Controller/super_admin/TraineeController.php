<?php

namespace App\Controller\super_admin;

use App\Entity\Company;
use App\Entity\Files;
use App\Entity\Person;
use App\Form\TraineeType;
use App\Repository\CompanyRepository;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

            // *************  Supprimer le CV s'il y en a déjà un ***************
            if ($traineeForm->has('cv')) {
                $oldFiles = $person->getFiles();
                foreach ($oldFiles as $oldFile) {
                    if ('CV' == $oldFile->getLabel()) {
                        $filePath = ($this->getParameter('kernel.project_dir') . '/files/' . $oldFile->getFile());
                        if (file_exists($oldFile->getFile())) {
                            unlink($filePath);
                        }
                        $entityManager->remove($oldFile);
                    }
                }
                // *************  Et ajouter le nouveau ***************
                $cvFile = $traineeForm->get('cv')->getData();
                if ($cvFile) {
                    $cvFilename = 'CV.' . $person->getFirstName() . '-' . $person->getLastName() . '.' . $cvFile->guessExtension();
                    $cvHash = hash('sha256', $cvFilename);
                    $cvHashFile = $cvHash . '.' . $cvFile->guessExtension();
                    $cvFile->move($this->getParameter('kernel.project_dir') . '/files/', $cvFilename);
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
            // *************  Supprimer la lettre de motivation s'il y en a déjà une et ajouter le nouveau ***************
            if ($traineeForm->has('coverLetter')) {
                $oldFiles = $person->getFiles();
                foreach ($oldFiles as $oldFile) {
                    if ('LM' == $oldFile->getLabel()) {
                        $filePath = ($this->getParameter('kernel.project_dir') . '/files/' . $oldFile->getFile());
                        if (file_exists($oldFile->getFile())) {
                            unlink($filePath);
                        }
                        $entityManager->remove($oldFile);
                    }
                }
                // *************  Et ajouter la nouvelle ***************
                $lmFile = $traineeForm->get('coverLetter')->getData();
                if ($lmFile) {
                    $lmFilename = 'LM.' . $person->getFirstName() . '-' . $person->getLastName() . '.' . $lmFile->guessExtension();
                    $cvHash = hash('sha256', $lmFilename);
                    $cvHashFile = $cvHash . '.' . $lmFile->guessExtension();
                    $lmFile->move($this->getParameter('kernel.project_dir') . '/files/' . $lmFilename);
                    $file = new Files();
                    $file->setLabel('LM')
                        ->setFile($cvHashFile)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setUpdatedAt(new \DateTimeImmutable())
                        ->setPerson($person)
                        ->setRealFileName($lmFilename);

                    $entityManager->persist($file);
                }
            }
            // *************  Supprimer la convention de stage s'il y en a déjà une ***************
            if ($traineeForm->has('internshipAgreement')) {
                $oldFiles = $person->getFiles();
                foreach ($oldFiles as $oldFile) {
                    if ('CS' == $oldFile->getLabel()) {
                        $filePath = ($this->getParameter('kernel.project_dir') . '/files/' . $oldFile->getFile());
                        if (file_exists($oldFile->getFile())) {
                            unlink($filePath);
                        }
                        $entityManager->remove($oldFile);
                    }
                }
                // *************  Et ajouter la nouvelle ***************
                $csFile = $traineeForm->get('internshipAgreement')->getData();
                if ($csFile) {
                    $csFilename = 'CS.' . $person->getFirstName() . '-' . $person->getLastName() . '.' . $csFile->guessExtension();
                    $cvHash = hash('sha256', $csFilename);
                    $cvHashFile = $cvHash . '.' . $csFile->guessExtension();
                    $csFile->move($this->getParameter('kernel.project_dir') . '/files/' . $csFilename);
                    $file = new Files();
                    $file->setLabel('CS')
                        ->setFile($cvHashFile)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setUpdatedAt(new \DateTimeImmutable())
                        ->setPerson($person)
                        ->setRealFileName($csFilename);

                    $entityManager->persist($file);
                }
            }
//            $entityManager->persist($person);
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
