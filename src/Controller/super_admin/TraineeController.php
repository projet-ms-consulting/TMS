<?php

namespace App\Controller\super_admin;

use App\Entity\Files;
use App\Entity\Person;
use App\Form\TraineeType;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function edit(Request $request, Person $personne, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $person = $user->getPerson();

        $traineeForm = $this->createForm(TraineeType::class, $personne);
        $traineeForm->handleRequest($request);

        if ($traineeForm->isSubmitted() && $traineeForm->isValid()) {
            $personne->setSchoolSupervisor(null)
                ->setCompanyReferent(null)
                ->setManager(null)
                ->setInternshipSupervisor(null);

            $personne->setUpdatedAt(new \DateTimeImmutable())
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
                $label = 'CV';
                $file = $this->editFile($label, $personne, $entityManager);
                $fileGiven = $traineeForm->get('cv')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $file = $this->newFile($label, $personne, $file, $fileGiven);
                    $entityManager->persist($file);
                }
            }

            if ($traineeForm->has('coverLetter')) {
                $label = 'LM';
                $file = $this->editFile($label, $personne, $entityManager);
                $fileGiven = $traineeForm->get('coverLetter')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $file = $this->newFile($label, $personne, $file, $fileGiven);
                    $entityManager->persist($file);
                }
            }

            if ($traineeForm->has('internshipAgreement')) {
                $label = 'CS';
                $file = $this->editFile($label, $personne, $entityManager);
                $fileGiven = $traineeForm->get('internshipAgreement')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $file = $this->newFile($label, $personne, $file, $fileGiven);
                    $entityManager->persist($file);
                }
            }
            $entityManager->persist($personne);
            $entityManager->flush();
            $this->addFlash('success', 'Modification réussie !');

            return $this->redirectToRoute('super_admin_app_trainee_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/trainee/edit.html.twig', [
            'personne' => $personne,
            'traineeForm' => $traineeForm,
            'connectedPerson' => $person,
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

    public function newFile(string $label, Person $personne, Files $file, mixed $fileGiven): Files
    {
        $fileName = $label.$personne->getFirstName().'-'.$personne->getLastName().'.'.$fileGiven->guessExtension();
        $fileHash = hash('sha256', $fileName);
        $file->setFile($fileHash)
            ->setLabel($label)
            ->setPerson($personne)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setRealFileName($fileName);
        $fileGiven->move(
            $this->getParameter('kernel.project_dir').'/files/'.$personne->getId().'/', $fileName
        );

        return $file;
    }

    public function editFile(string $label, Person $personne, EntityManagerInterface $entityManager)
    {
        $oldFiles = $personne->getFiles();
        foreach ($oldFiles as $file) {
            if ($label === $file->getLabel()) {
                $filePath = ($this->getParameter('kernel.project_dir').'/files/'.$personne->getId().'/'.$file->getFile());
                if (file_exists($file->getFile())) {
                    unlink($filePath);

                }
                $entityManager->remove($file);
            }
        }

        return null;
    }
}
