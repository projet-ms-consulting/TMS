<?php

namespace App\Controller\super_admin;

use App\Entity\Files;
use App\Entity\Person;
use App\Entity\User;
use App\Form\PersonType;
use App\Repository\FilesRepository;
use App\Repository\PersonRepository;
use App\Service\PasswordGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
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
    private PasswordGenerator $passwordGenerator;
    private MailerInterface $mailer;

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
            $entityManager->persist($personne);
            $entityManager->flush();
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

                $email = (new TemplatedEmail())
                    ->from(new Address('noreply@msconsulting-europe.com', 'MS_Consulting'))
                    ->to($user->getEmail())
                    ->subject('Bienvenue sur TMS')
                    ->htmlTemplate('person/new.html.twig')
                    ->context(['user' => $user, 'password' => $password]);
                try {
                    $this->mailer->send($email);
                    $this->addFlash('success', 'Email envoyé avec succès !');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email : '.$e->getMessage());
                }
            }

            // *************  Upload CV ***************
            if ($personForm->has('cv')) {
                $fileGiven = $personForm->get('cv')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $label = 'CV';
                    $file = $this->newFile($label, $personne, $file, $fileGiven);

                    $entityManager->persist($file);
                }
            }

            // *************  Upload Lettre de Motivation ***************
            if ($personForm->has('coverLetter')) {
                $fileGiven = $personForm->get('coverLetter')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $label = 'LM';
                    $file = $this->newFile($label, $personne, $file, $fileGiven);

                    $entityManager->persist($file);
                }
            }
            // *************  Upload convention de stage ****************
            if ($personForm->has('internshipAgreement')) {
                $fileGiven = $personForm->get('internshipAgreement')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $label = 'CS';
                    $file = $this->newFile($label, $personne, $file, $fileGiven);

                    $entityManager->persist($file);
                }
            }

            $this->addFlash('success', 'Création réussie !');

            $entityManager->persist($user);
            $entityManager->flush();

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
    public function show(Person $personne, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $person = $user->getPerson();

        return $this->render('super_admin/person/show.html.twig', [
            'connectedPerson' => $person,
            'personne' => $personne,
            'user' => $personne->getUser(),
            'files' => $personne->getFiles(),
        ]);
    }

    #[Route('/file/{id}/{name}', name: 'show_file', methods: ['GET'])]
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
                    ->setRoles([$personForm->get('roles')->getData()]);
            }

            if ($personForm->has('cv')) {
                $label = 'CV';
                $file = $this->editFile($label, $personne, $entityManager);
                $fileGiven = $personForm->get('cv')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $file = $this->newFile($label, $personne, $file, $fileGiven);
                    $entityManager->persist($file);
                }
            }

            if ($personForm->has('coverLetter')) {
                $label = 'LM';
                $file = $this->editFile($label, $personne, $entityManager);
                $fileGiven = $personForm->get('coverLetter')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $file = $this->newFile($label, $personne, $file, $fileGiven);
                    $entityManager->persist($file);
                }
            }

            if ($personForm->has('internshipAgreement')) {
                $label = 'CS';
                $file = $this->editFile($label, $personne, $entityManager);
                $fileGiven = $personForm->get('internshipAgreement')->getData();
                if ($fileGiven) {
                    $file = new Files();
                    $file = $this->newFile($label, $personne, $file, $fileGiven);
                    $entityManager->persist($file);
                }
            }

            $entityManager->persist($personne);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Modification effectuée!');

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
        }
        $this->addFlash('success', 'Suppression réussie !');

        return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
    }

    public function getData(FormInterface $personForm, Person $personne, EntityManagerInterface $entityManager): mixed
    {
        $personne->setCompany(null)
            ->setSchool(null)
            ->setStartInternship(null)
            ->setEndInternship(null)
            ->setSchoolSupervisor(null)
            ->setCompanyReferent(null)
            ->setManager(null)
            ->setInternshipSupervisor(null);

        if ($personForm->has('companyReferent')) {
            $personne->setCompanyReferent($personne);
            $personne->setCompany($personForm->get('companyReferent')->getData());
        }
        if ($personForm->has('manager')) {
            $personne->setManager($personne);
            $personne->setCompany($personForm->get('manager')->getData());
        }
        if ($personForm->has('internshipSupervisor')) {
            $personne->setInternshipSupervisor($personne);
            $personne->setCompany($personForm->get('internshipSupervisor')->getData());
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
            $personne->setSchoolSupervisor($personne);
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
            $companyReferent = $personForm->get('stagiaireRefEntrep')->getData();
            $personne->setCompanyReferent($companyReferent);
        }

        // Attribuer le référent école au stagiaire
        if ($personForm->has('traineeRefSchool')) {
            $schoolSupervisor = $personForm->get('traineeRefSchool')->getData();
            $personne->setSchoolSupervisor($schoolSupervisor);
        }

        // Attribuer le chef d'entreprise au stagiaire
        if ($personForm->has('stagiaireManager')) {
            $manager = $personForm->get('stagiaireManager')->getData();
            $personne->setManager($manager);
        }
        // Attribuer le maître de stage au stagiaire
        if ($personForm->has('traineeSupervisor')) {
            $internshipSupervisor = $personForm->get('traineeSupervisor')->getData();
            $personne->setInternshipSupervisor($internshipSupervisor);
        }

        return $personne;
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
