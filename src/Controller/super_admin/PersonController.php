<?php

namespace App\Controller\super_admin;

use App\Entity\Files;
use App\Entity\Person;
use App\Entity\User;
use App\Form\PersonType;
use App\Repository\FilesRepository;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('super_admin/person', name: 'super_admin_app_person_')]
class PersonController extends AbstractController
{
    #[Route('/index', name: 'index', methods: ['GET'])]
    public function index(PersonRepository $personRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 8);
        $sort = $request->query->get('sort', 'a.id');
        $direction = $request->query->get('direction', 'asc');
        $person = $personRepository->paginatePerson($page, $limit);
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/person/index.html.twig', [
            'personne' => $person,
            'person' => $personne,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $personne = new Person();
        $user = $this->getUser();
        $person = $user->getPerson();

        $personForm = $this->createForm(PersonType::class, $personne);
        $personForm->handleRequest($request);

        if ($personForm->isSubmitted() && $personForm->isValid()) {
//            dump($personne);
//            dd($request->request->all());
            $personne->setCreatedAt(new \DateTimeImmutable());

            if ($personForm->has('companyReferent')) {
                $personne->setCompany($personForm->get('companyReferent')->getData());
            }
            if ($personForm->has('manager')) {
                $personne->setCompany($personForm->get('manager')->getData());
            }
            if ($personForm->has('internshipSupervisor')) {
                $personne->setCompany($personForm->get('internshipSupervisor')->getData());
            }
            if ($personForm->has('schoolSupervisor')) {
                $personne->setSchool($personForm->get('schoolSupervisor')->getData());
            }
            if ($personForm->has('startInternship')) {
                $personne->setStartInternship($personForm->get('startInternship')->getData());
            }
            if ($personForm->has('endInternship')) {
                $personne->setEndInternship($personForm->get('endInternship')->getData());
            }
            if ($personForm->has('stagiaireCompany')) {
                $personne->setCompany($personForm->get('stagiaireCompany')->getData());
            }
//           if ($personForm->has('stagiaireRefEntrep')) {
//               $personne->setCompany($personForm->get('stagiaireRefEntrep')->getData());
//           }
            if ($personForm->has('traineeSchool')) {
                $personne->setSchool($personForm->get('traineeSchool')->getData());
            }
//            if ($personForm->has('traineeRefSchool')) {
//                $personne->setSchool($personForm->get('traineeRefSchool')->getData());
//            }

            $roles = $personForm->get('roles')->getData();
            if (is_string($roles)) {
                $roles = [$roles];
            }
            $personne->setRoles($roles);

            if ($personForm->has('checkUser') && $personForm->get('checkUser')->getData()) {
                $user = new User();
                $user->setCreatedAt(new \DateTimeImmutable())
                    ->setCanLogin($personForm->get('checkUser')->getData())
                    ->setEmail($personForm->get('email')->getData())
                    ->setRoles($roles);

                $hashedPassword = $passwordHasher->hashPassword($user, $personForm->get('password')->getData());
                $user->setPassword($hashedPassword);

                $personne->setUser($user);
                $entityManager->persist($user);
            }


            // *************  Upload CV ***************
            if ($personForm->has('cv')) {
                $cvFile = $personForm->get('cv')->getData();
                if ($cvFile) {
                    $cvFilename = 'CV.' . $personne->getFirstName() . '-' . $personne->getLastName() . '.' . $cvFile->guessExtension();
                    $cvHash = hash('sha256', $cvFilename);
                    $cvHashFile = $cvHash . '.' . $cvFile->guessExtension();
                    $cvFile->move($this->getParameter('kernel.project_dir') . '/files/' , $cvFilename);

                    $file = new Files();
                    $file->setLabel('CV')
                        ->setFile($cvHashFile)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setPerson($person)
                        ->setRealFileName($cvFilename);

                    $entityManager->persist($file);
                    $entityManager->flush();
                }
            }
            // *************  Upload Lettre de Motivation ***************
            if ($personForm->has('coverLetter')) {
                $lmFile = $personForm->get('coverLetter')->getData();
                if ($lmFile) {
                    $lmFilename = 'LM.'.$personne->getFirstName().'-'.$personne->getLastName().'.'.$lmFile->guessExtension();
                    $lmHash = hash('sha256', $lmFilename);
                    $lmHashFile = $lmHash.'.'.$lmFile->guessExtension();
                    $lmFile->move($this->getParameter('kernel.project_dir') . '/files/' , $lmFilename);

                    $file = new Files();
                    $file->setLabel('LM')
                        ->setFile($lmHashFile)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setPerson($personne)
                        ->setRealFileName($lmFilename);

                    $entityManager->persist($file);
                    $entityManager->flush();
                }
            }
            // *************  Upload convention de stage ****************
            if ($personForm->has('internshipAgreement')) {
                $csFile = $personForm->get('internshipAgreement')->getData();
                if ($csFile) {
                    $csFilename = 'CS.' . $personne->getFirstName() . '-' . $personne->getLastName() . '.' . $csFile->guessExtension();
                    $csHash = hash('sha256', $csFilename);
                    $csHashFile = $csHash . '.' . $csFile->guessExtension();
                    $csFile->move($this->getParameter('kernel.project_dir') . 'files/' , $csFilename);

                    $file = new Files();
                    $file->setLabel('CS')
                        ->setFile($csHashFile)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setPerson($personne)
                        ->setRealFileName($csFilename);

                    $entityManager->persist($file);
                    $entityManager->flush();
                }
            }
            //            dump($personne);
//            dd($request->request->all());
            $entityManager->persist($personne);
            $entityManager->flush();
            $this->addFlash('success', 'Création réussie !');

            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/person/new.html.twig', [
            'person' => $person,
            'personne' => $personne,
            'personForm' => $personForm,
            'files' => $personne->getFiles(),
            'roles' => $personne->getRoles(),
        ]);
}


    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Person $person, FilesRepository $file, EntityManagerInterface $entityManager, $id): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/person/show.html.twig', [
            'personne' => $person,
            'person' => $personne,
            'user' => $person->getUser(),
            'files' => $person->getFiles(),
        ]);
    }

    #[Route('/file/{id}', name: 'show_file', methods: ['GET'])]
    public function showFile(FilesRepository $file, EntityManagerInterface $entityManager, $id): BinaryFileResponse
    {
        $file = $entityManager->getRepository(Files::class)->find($id);

        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/files/' . $file->getRealFileName();

        return $this->file($filePath, $file->getRealFileName(), ResponseHeaderBag::DISPOSITION_INLINE);
    }


    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $person, FilesRepository $file, EntityManagerInterface $entityManager): Response
    {
        $file = new Files();
        $user = $this->getUser();
        $personne = $user->getPerson();

        $personForm = $this->createForm(PersonType::class, $personne);
        $personForm->handleRequest($request);

        if ($personForm->isSubmitted() && $personForm->isValid()) {
            $person->setUpdatedAt(new \DateTimeImmutable());

            if ($request->files->get('person')['cv']) {
                $oldFiles = $person->getFiles();
                foreach ($oldFiles as $oldFile) {
                    if ('CV' == $oldFile->getLabel()) {
                        $filePath = ($this->getParameter('kernel.project_dir') . '/files/' .$oldFile->getFile());
                        if (file_exists($oldFile->getFile())) {
                            unlink($filePath);
                        }
                        $entityManager->remove($oldFile);
                    }
                }
                $cvFile = $request->files->get('person')['cv'];
                $cvFilename = 'CV.'.$person->getFirstName().'-'.$person->getLastName().'.'.$cvFile->guessExtension();
                $cvHash = hash('sha256', $cvFilename);
                $cvHashFile = $cvHash.'.'.$cvFile->guessExtension();
                $cvFile->move($this->getParameter('kernel.project_dir') . '/files/', $cvFilename);
                    $file = new Files();
                    $file->setLabel('CV')
                        ->setFile($cvHashFile)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setUpdatedAt(new \DateTimeImmutable())
                        ->setPerson($person)
                        ->setRealFileName($cvFilename);

                    $entityManager->persist($file);
                    $entityManager->flush();
            }

            if ($request->files->get('person')['coverLetter']) {
                $oldFiles = $person->getFiles();
                foreach ($oldFiles as $oldFile) {
                    if ('LM' == $oldFile->getLabel()) {
                        $filePath = ($this->getParameter('kernel.project_dir') . '/files/' .$oldFile->getFile());
                        if (file_exists($oldFile->getFile())) {
                            unlink($filePath);
                        }
                        $entityManager->remove($oldFile);
                    }
                }
                $lmFile = $request->files->get('person')['coverLetter'];
                $lmFilename = 'LM.'.$person->getFirstName().'-'.$person->getLastName().'.'.$lmFile->guessExtension();
                $cvHash = hash('sha256', $lmFilename);
                $cvHashFile = $cvHash.'.'.$lmFile->guessExtension();
                $lmFile->move($this->getParameter('kernel.project_dir') . '/files/' . $lmFilename);
                    $file = new Files();
                    $file->setLabel('LM')
                        ->setFile($cvHashFile)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setUpdatedAt(new \DateTimeImmutable())
                        ->setPerson($person)
                        ->setRealFileName($lmFilename);

                    $entityManager->persist($file);
                    $entityManager->flush();
            }

            if ($request->files->get('person')['internshipAgreement']) {
                $oldFiles = $person->getFiles();
                foreach ($oldFiles as $oldFile){
                    if($oldFile->getLabel() == 'CS') {
                        $filePath = ($this->getParameter('kernel.project_dir') . '/files/' . $oldFile->getFile());
                        if (file_exists($oldFile->getFile())) {
                            unlink($filePath);
                        }
                        $entityManager->remove($oldFile);
                    }
                }
                $csFile = $request->files->get('person')['internshipAgreement'];
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
                    $entityManager->flush();
            }

            $entityManager->persist($personne);
            $entityManager->flush();

            $this->addFlash('success', 'Modification réussie !');

            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/person/edit.html.twig', [
            'person' => $person,
            'personne' => $person,
            'personForm' => $personForm->createView(),
            'files' => $person->getFiles(),
            'user' => $person->getUser(),
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

        return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
    }
}
