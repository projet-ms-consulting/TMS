<?php

namespace App\Controller\super_admin;

use App\Entity\Address;
use App\Entity\School;
use App\Form\SchoolEditType;
use App\Form\SchoolType;
use App\Repository\PersonRepository;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/super_admin/school', name: 'super_admin_app_school_')]
class SchoolController extends AbstractController
{
    #[Route('/index', name: 'index', methods: ['GET'])]
    public function index(SchoolRepository $schoolRepository, Request $request ,PersonRepository $personRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 8);
        $sort = $request->query->get('sort', 'a.id');
        $direction = $request->query->get('direction', 'asc');
        $schools = $schoolRepository->paginateSchools($page, $limit);
        $personne = $personRepository->findAll();

        $user = $this->getUser();
        $person = $user->getPerson();


        return $this->render('super_admin/school/index.html.twig', [
            'schools' => $schools,
            'connectedPerson' => $person,
            'personne' => $personne,

        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $school = new School();
        $user = $this->getUser();
        $person = $user->getPerson();
        $form = $this->createForm(SchoolType::class, $school);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['school'];

            if (0 == $data['checkAddress']) {

                $newAddress = new Address();;
                $newAddress->setCreatedAt(new \DateTimeImmutable());

                $entityManager->persist($newAddress);
                $school->setAddress($newAddress);
            }


            if (2 == $data['checkAddress']) {
                $nbStreet = $data['nbStreetNewAddress'];
                $street = $data['streetNewAddress'];
                $city = $data['cityNewAddress'];
                $zipCode = $data['zipCodeNewAddress'];

                $newAddress = new Address();
                $newAddress->setNbStreet($nbStreet);
                $newAddress->setStreet($street);
                $newAddress->setCity($city);
                $newAddress->setZipCode($zipCode);
                $newAddress->setCreatedAt(new \DateTimeImmutable());

                $entityManager->persist($newAddress);
                $school->setAddress($newAddress);
            }

            $school->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($school);
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_app_school_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/school/new.html.twig', [
            'school' => $school,
            'form' => $form->createView(),
            'connectedPerson' => $person,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(School $school): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/school/show.html.twig', [
            'school' => $school,
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, School $school, EntityManagerInterface $entityManager): Response
    {
        $address = $school->getAddress();
        $nbStreet = $school->getAddress()->getNbStreet();
        $street = $school->getAddress()->getStreet();
        $zipCode = $school->getAddress()->getZipCode();
        $city = $school->getAddress()->getCity();
        $user = $this->getUser();
        $personne = $user->getPerson();
        $form = $this->createForm(SchoolEditType::class, $school);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $school->setUpdatedAt(new \DateTimeImmutable());
            $address->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_app_school_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/school/edit.html.twig', [
            'company' => $school,
            'form' => $form->createView(),
            'connectedPerson' => $personne,
            'address' => $address,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, School $school, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$school->getId(), $request->request->get('_token'))) {
            $entityManager->remove($school);
            $entityManager->flush();
        }

        return $this->redirectToRoute('super_admin_app_school_index', [], Response::HTTP_SEE_OTHER);
    }
}
