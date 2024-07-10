<?php

namespace App\Controller\super_admin;

use App\Entity\Files;
use App\Entity\Person;
use App\Form\PersonType;
use App\Repository\FilesRepository;
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
    public function index(PersonRepository $personRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 8);
        $sort = $request->query->get('sort', 'a.id');
        $direction = $request->query->get('direction', 'asc');
        $person = $personRepository->paginatePerson($page, $limit);

        return $this->render('super_admin/person/index.html.twig', [
            'personne' => $person,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $person = new Person();

        $personForm = $this->createForm(PersonType::class, $person);
        $personForm->handleRequest($request);

        if ($personForm->isSubmitted() && $personForm->isValid()) {
            $person->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($person);
            $entityManager->flush();

            // *************  Upload CV ***************
            $cvFile = $personForm->get('cv')->getData();
            if ($cvFile) {
                $cvFilename = 'CV.'.$person->getFirstName().'-'.$person->getLastName().'.'.$cvFile->guessExtension();
                $cvHash = hash('sha256', $cvFilename);
                $cvHashFile = $cvHash.'.'.$cvFile->guessExtension();
                $cvFile->move($this->getParameter('kernel.project_dir').'/documents', $cvFilename);

                $file = new Files();
                $file->setLabel('CV')
                    ->setFile($cvHashFile)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setPerson($person)
                    ->setRealFileName($cvFilename);

                $entityManager->persist($file);
                $entityManager->flush();
            }
            // *************  Upload Lettre de motivation ****************
            $lmFile = $personForm->get('coverLetter')->getData();
            if ($lmFile) {
                $lmFilename = 'LM.'.$person->getFirstName().'-'.$person->getLastName().'.'.$lmFile->guessExtension();
                $lmHash = hash('sha256', $lmFilename);
                $lmHashFile = $lmHash.'.'.$lmFile->guessExtension();
                $lmFile->move($this->getParameter('kernel.project_dir').'/documents', $lmFilename);

                $file = new Files();
                $file->setLabel('LM')
                    ->setFile($lmHashFile)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setPerson($person)
                    ->setRealFileName($lmFilename);

                $entityManager->persist($file);
                $entityManager->flush();
            }
            // *************  Upload convention de stage ****************
            //            $csFile = $personForm->get('internshipAgreement')->getData();
            //            if ($csFile) {
            //                $csFilename = 'CS.' . $person->getFirstName() . '-' . $person->getLastName() . '.' . $csFile->guessExtension();
            //                $csHash = hash('sha256', $csFilename);
            //                $csHashFile = $csHash . '.' . $csFile->guessExtension();
            //                $csFile->move($this->getParameter('kernel.project_dir') . '/documents', $csHashFile);
            //
            //                $file= new Files();
            //                $file->setLabel('CS')
            //                    ->setFile($csHashFile)
            //                    ->setCreatedAt(new \DateTimeImmutable())
            //                    ->setPerson($person)
            //                    ->setRealFileName($csFilename);
            //
            //                $entityManager->persist($file);
            //                $entityManager->flush();
            //            }

            $entityManager->persist($person);
            $entityManager->flush();
            $this->addFlash('success', 'Création réussie !');

            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/person/new.html.twig', [
            'person' => $person,
            'personForm' => $personForm,
            'files' => $person->getFiles(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Person $person): Response
    {
        return $this->render('super_admin/person/show.html.twig', [
            'person' => $person,
            'user' => $person->getUser(),
            'files' => $person->getFiles(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $person, FilesRepository $file, EntityManagerInterface $entityManager): Response
    {
        $file = new Files();
        $personForm = $this->createForm(PersonType::class, $person);
        $personForm->handleRequest($request);

        if ($personForm->isSubmitted() && $personForm->isValid()) {
            $person->setUpdatedAt(new \DateTimeImmutable());

            if ($request->files->get('person')['cv']) {
                $oldFiles = $person->getFiles();
                foreach ($oldFiles as $oldFile) {
                    if ('CV' == $oldFile->getLabel()) {
                        $filePath = ($this->getParameter('kernel.project_dir').'/documents/'.$oldFile->getFile());
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
                $cvFile->move($this->getParameter('kernel.project_dir').'/documents', $cvFilename);
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
                        $filePath = ($this->getParameter('kernel.project_dir').'/documents/'.$oldFile->getFile());
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
                $lmFile->move($this->getParameter('kernel.project_dir').'/documents', $lmFilename);
                $file->setLabel('LM')
                    ->setFile($cvHashFile)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setUpdatedAt(new \DateTimeImmutable())
                    ->setPerson($person)
                    ->setRealFileName($lmFilename);

                $entityManager->persist($file);
                $entityManager->flush();
            }

            //            if ($request->files->get('person')['internshipAgreement']) {
            //                $oldFiles = $person->getFiles();
            //                foreach ($oldFiles as $oldFile){
            //                    if($oldFile->getLabel() == 'CS') {
            //                        $filePath = ($this->getParameter('kernel.project_dir') . '/documents/' . $oldFile->getFile());
            //                        if (file_exists($oldFile->getFile())) {
            //                            unlink($filePath);
            //                        }
            //                        $entityManager->remove($oldFile);
            //
            //                    }
            //                }
            //                $csFile = $request->files->get('person')['internshipAgreement'];
            //                $csFilename = 'CS.' . $person->getFirstName() . '-' . $person->getLastName() . '.' . $csFile->guessExtension();
            //                $cvHash = hash('sha256', $csFilename);
            //                $cvHashFile = $cvHash . '.' . $csFile->guessExtension();
            //                $csFile->move($this->getParameter('kernel.project_dir') . '/documents', $csFilename);
            //                $file->setLabel('CS')
            //                    ->setFile($cvHashFile)
            //                    ->setCreatedAt(new \DateTimeImmutable())
            //                    ->setUpdatedAt(new \DateTimeImmutable())
            //                    ->setPerson($person)
            //                    ->setRealFileName($csFilename);
            //
            //                $entityManager->persist($file);
            //                $entityManager->flush();
            //            }

            $entityManager->persist($person);
            $entityManager->flush();

            $this->addFlash('success', 'Modification réussie !');

            return $this->redirectToRoute('super_admin_app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/person/edit.html.twig', [
            'person' => $person,
            'personForm' => $personForm,
            'files' => $person->getFiles(),
            'user' => $person->getUser(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['GET', 'POST'])]
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
