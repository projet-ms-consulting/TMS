<?php

namespace App\Controller\super_admin;

use App\Entity\Files;
use App\Entity\Person;
use App\Entity\User;
use App\Form\PersonType;
use App\Repository\PersonRepository;
use App\Service\PasswordGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('super_admin/person', name: 'super_admin_app_person_')]
class PersonController extends AbstractController
{
    private $passwordGenerator;
    private $mailer;

    public function __construct(PasswordGenerator $passwordGenerator, MailerInterface $mailer)
    {
        $this->passwordGenerator = $passwordGenerator;
        $this->mailer = $mailer;
    }

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
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $personne = new Person();
        $user = $this->getUser();
        $person = $user->getPerson();

        $personForm = $this->createForm(PersonType::class, $personne);
        $personForm->handleRequest($request);

        if ($personForm->isSubmitted() && $personForm->isValid()) {
            $personne->setCreatedAt(new \DateTimeImmutable());

            $this->getData($personForm, $personne, $entityManager);

            if ($personForm->has('checkUser') && $personForm->get('checkUser')->getData()) {
                $user = new User();
                $user->setCreatedAt(new \DateTimeImmutable())
                    ->setCanLogin($personForm->get('checkUser')->getData())
                    ->setEmail($personForm->get('email')->getData())
                    ->setRoles([$personForm->get('roles')->getData()]);

                $password = $this->passwordGenerator->generatePassword();
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
                $personne->setUser($user);
                $entityManager->persist($user);
            }

            // *************  Upload CV ***************
            if ($personForm->has('cv')) {
                $cvFile = $personForm->get('cv')->getData();
                if ($cvFile) {
                    $cvFilename = 'CV.'.$personne->getFirstName().'-'.$personne->getLastName().'.'.$cvFile->guessExtension();
                    $cvHash = hash('sha256', $cvFilename);
                    $cvHashFile = $cvHash.'.'.$cvFile->guessExtension();
                    $cvFile->move($this->getParameter('kernel.project_dir').'/files/', $cvFilename);

                    $file = new Files();
                    $file->setLabel('CV')
                        ->setFile($cvHashFile)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setPerson($personne)
                        ->setRealFileName($cvFilename);

                    $entityManager->persist($file);
                }
            }
            // *************  Upload Lettre de Motivation ***************
            if ($personForm->has('coverLetter')) {
                $lmFile = $personForm->get('coverLetter')->getData();
                if ($lmFile) {
                    $lmFilename = 'LM.'.$personne->getFirstName().'-'.$personne->getLastName().'.'.$lmFile->guessExtension();
                    $lmHash = hash('sha256', $lmFilename);
                    $lmHashFile = $lmHash.'.'.$lmFile->guessExtension();
                    $lmFile->move($this->getParameter('kernel.project_dir').'/files/', $lmFilename);

                    $file = new Files();
                    $file->setLabel('LM')
                        ->setFile($lmHashFile)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setPerson($personne)
                        ->setRealFileName($lmFilename);

                    $entityManager->persist($file);
                }
            }
            // *************  Upload convention de stage ****************
            if ($personForm->has('internshipAgreement')) {
                $csFile = $personForm->get('internshipAgreement')->getData();
                if ($csFile) {
                    $csFilename = 'CS.'.$personne->getFirstName().'-'.$personne->getLastName().'.'.$csFile->guessExtension();
                    $csHash = hash('sha256', $csFilename);
                    $csHashFile = $csHash.'.'.$csFile->guessExtension();
                    $csFile->move($this->getParameter('kernel.project_dir').'files/', $csFilename);

                    $file = new Files();
                    $file->setLabel('CS')
                        ->setFile($csHashFile)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setPerson($personne)
                        ->setRealFileName($csFilename);

                    $entityManager->persist($file);
                }
            }

            $entityManager->persist($personne);
            $entityManager->flush();

            $this->addFlash('success', 'Création réussie !');
            $email = (new TemplatedEmail())
                ->from(new Address('noreply@msconsulting-europe.com', 'MS_Consulting'))
                ->to($user->getEmail())
                ->subject('Bienvenue sur TMS')
                ->htmlTemplate('person/new.html.twig')
                ->context(['user' => $user, 'password' => $password])
            ;

            try {
                $this->mailer->send($email);
                $this->addFlash('success', 'Email envoyé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email : '.$e->getMessage());
            }

            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/person/new.html.twig', [
            'connectedPerson' => $person,
            'personne' => $personne,
            'files' => $personne->getFiles(),
            'roles' => $personne->getRoles(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Person $person, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/person/show.html.twig', [
            'connectedPerson' => $personne,
            'person' => $person,
            'user' => $person->getUser(),
            'files' => $person->getFiles(),
        ]);
    }

    #[Route('/file/{id}', name: 'show_file', methods: ['GET'])]
    public function showFile(EntityManagerInterface $entityManager, $id): BinaryFileResponse
    {
        $file = $entityManager->getRepository(Files::class)->find($id);

        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }
        $filePath = $this->getParameter('kernel.project_dir').'/files/'.$file->getRealFileName();

        return $this->file($filePath, $file->getRealFileName(), ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $personne, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $person = $user->getPerson();

        $personForm = $this->createForm(PersonType::class, $personne);
        $personForm->handleRequest($request);

        if ($personForm->isSubmitted() && $personForm->isValid()) {
            $personne->setUpdatedAt(new \DateTimeImmutable());

            $this->getData($personForm, $personne, $entityManager);

            if ($personForm->has('checkUser') && $personForm->get('checkUser')->getData()) {
                $user = new User();
                $user->setCreatedAt(new \DateTimeImmutable())
                    ->setCanLogin($personForm->get('checkUser')->getData())
                    ->setEmail($personForm->get('email')->getData())
                    ->setRoles($personForm->get('roles')->getData());
            }

            if ($personForm->has('cv')) {
                $oldFiles = $personne->getFiles();
                foreach ($oldFiles as $oldFile) {
                    if ('CV' == $oldFile->getLabel()) {
                        $filePath = ($this->getParameter('kernel.project_dir').'/files/'.$oldFile->getFile());
                        if (file_exists($oldFile->getFile())) {
                            unlink($filePath);
                        }
                        $entityManager->remove($oldFile);
                    }
                }
                $cvFile = $request->files->get('person')['cv'];
                $cvFilename = 'CV.'.$personne->getFirstName().'-'.$personne->getLastName().'.'.$cvFile->guessExtension();
                $cvHash = hash('sha256', $cvFilename);
                $cvHashFile = $cvHash.'.'.$cvFile->guessExtension();
                $cvFile->move($this->getParameter('kernel.project_dir').'/files/', $cvFilename);
                $file = new Files();
                $file->setLabel('CV')
                    ->setFile($cvHashFile)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setUpdatedAt(new \DateTimeImmutable())
                    ->setPerson($personne)
                    ->setRealFileName($cvFilename);

                $entityManager->persist($file);
            }

            if ($personForm->has('coverLetter')) {
                //            if ($request->files->get('person')['coverLetter']) {
                $oldFiles = $personne->getFiles();
                foreach ($oldFiles as $oldFile) {
                    if ('LM' == $oldFile->getLabel()) {
                        $filePath = ($this->getParameter('kernel.project_dir').'/files/'.$oldFile->getFile());
                        if (file_exists($oldFile->getFile())) {
                            unlink($filePath);
                        }
                        $entityManager->remove($oldFile);
                    }
                }
                $lmFile = $request->files->get('person')['coverLetter'];
                $lmFilename = 'LM.'.$personne->getFirstName().'-'.$personne->getLastName().'.'.$lmFile->guessExtension();
                $cvHash = hash('sha256', $lmFilename);
                $cvHashFile = $cvHash.'.'.$lmFile->guessExtension();
                $lmFile->move($this->getParameter('kernel.project_dir').'/files/'.$lmFilename);
                $file = new Files();
                $file->setLabel('LM')
                    ->setFile($cvHashFile)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setUpdatedAt(new \DateTimeImmutable())
                    ->setPerson($personne)
                    ->setRealFileName($lmFilename);

                $entityManager->persist($file);
            }

            if ($personForm->has('internshipAgreement')) {
                $oldFiles = $personne->getFiles();
                foreach ($oldFiles as $oldFile) {
                    if ('CS' == $oldFile->getLabel()) {
                        $filePath = ($this->getParameter('kernel.project_dir').'/files/'.$oldFile->getFile());
                        if (file_exists($oldFile->getFile())) {
                            unlink($filePath);
                        }
                        $entityManager->remove($oldFile);
                    }
                }
                $csFile = $request->files->get('person')['internshipAgreement'];
                $csFilename = 'CS.'.$personne->getFirstName().'-'.$personne->getLastName().'.'.$csFile->guessExtension();
                $cvHash = hash('sha256', $csFilename);
                $cvHashFile = $cvHash.'.'.$csFile->guessExtension();
                $csFile->move($this->getParameter('kernel.project_dir').'/files/'.$csFilename);
                $file = new Files();
                $file->setLabel('CS')
                    ->setFile($cvHashFile)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setUpdatedAt(new \DateTimeImmutable())
                    ->setPerson($personne)
                    ->setRealFileName($csFilename);

                $entityManager->persist($file);
            }
            $entityManager->persist($personne);
            $entityManager->flush();

            $this->addFlash('success', 'Modification réussie !');

//                        dd($personne);

            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/person/edit.html.twig', [
            'connectedPerson' => $person,
            'personne' => $personne,
            'files' => $personne->getFiles(),
            'user' => $personne->getUser(),
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

            $this->addFlash('success', 'Suppression réussie !');
        }

        return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
    }

    public function getData(FormInterface $personForm, Person $personne, EntityManagerInterface $entityManager): mixed
    {
        $personne->setCompany(null)
            ->setSchool(null)
            ->setStartInternship(null)
            ->setEndInternship(null)
            ->setSchoolSupervisor(null)
            ->setCompanyReferent(null);
//          ->setManager(null);

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
        if ($personForm->has('school')) {
            $personne->setSchool($personForm->get('school')->getData());
        }
        if ($personForm->has('roles')) {
            $personne->setRoles([$personForm->get('roles')->getData()]);
        }
        if ($personForm->has('traineeSchool')) {
            $personne->setSchool($personForm->get('traineeSchool')->getData());
        }
        // Attribuer le référent entreprise au stagiaire
        if ($personForm->has('stagiaireRefEntrep')) {
            $companyReferentName = $personForm->get('stagiaireRefEntrep')->getData();
            // Récupérer l'ID du référent entreprise
            $companyReferentId = is_numeric($companyReferentName) ? (int) $companyReferentName : null;
            if (null !== $companyReferentId) {
                // Rechercher l'objet Person correspondant à l'ID
                $companyReferent = $entityManager->getRepository(Person::class)->find($companyReferentId);
                if ($companyReferent) {
                    // Définir le référent entreprise sur personne
                    $personne->setCompanyReferent($companyReferent);
                }
            }
        }
        // Attribuer le référent école au stagiaire
        if ($personForm->has('traineeRefSchool')) {
            $schoolSupervisorName = $personForm->get('traineeRefSchool')->getData();
            // Récupérer l'ID du référent école
            $schoolSupervisorId = is_numeric($schoolSupervisorName) ? (int) $schoolSupervisorName : null;

            if (null !== $schoolSupervisorId) {
                // Rechercher l'objet Person correspondant à l'ID
                $schoolSupervisor = $entityManager->getRepository(Person::class)->find($schoolSupervisorId);
                if ($schoolSupervisor) {
                    // Définir le référent entreprise sur personne
                    $personne->setSchoolSupervisor($schoolSupervisor);
                }
            }
        }

        return $personne;
    }
}
