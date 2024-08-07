<?php

namespace App\Controller\super_admin;

use App\Entity\Address;
use App\Entity\Company;
use App\Form\CompanyEditType;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/super_admin/company', name: 'super_admin_app_company_')]
class CompanyController extends AbstractController
{
    #[Route('/index', name: 'index', methods: ['GET'])]
    public function index(CompanyRepository $companyRepository, Request $request, PersonRepository $personRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 8);
        $sort = $request->query->get('sort', 'a.id');
        $direction = $request->query->get('direction', 'asc');
        $companies = $companyRepository->paginateCompany($page, $limit);
        $user = $this->getUser();
        $personne = $user->getPerson();
        $person = $personRepository->findAll();

        return $this->render('super_admin/company/index.html.twig', [
            'companies' => $companies,
            'connectedPerson' => $personne,
            'personne' => $person,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $company = new Company();
        $user = $this->getUser();
        $person = $user->getPerson();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['company'];

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
                $company->setAddress($newAddress);
            }

            $company->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($company);
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/company/new.html.twig', [
            'company' => $company,
            'form' => $form->createView(),
            'connectedPerson' => $person,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Company $company): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/company/show.html.twig', [
            'company' => $company,
            'connectedPerson' => $personne,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        $address = $company->getAddress();
        $user = $this->getUser();
        $personne = $user->getPerson();

        $form = $this->createForm(CompanyEditType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['company_edit'];

            $nbStreet = $data['address']['nbStreet'];
            $street = $data['address']['street'];
            $city = $data['address']['city'];
            $zipCode = $data['address']['zipCode'];
            $employeeNumber = $data['employeeNumber'];

            if ('' != $nbStreet || '' != $street || '' != $city || '' != $zipCode) {
                if (null == $address) {
                    $address = new Address();
                    $newAddress = true;
                    $address->setCreatedAt(new \DateTimeImmutable());
                } else {
                    $newAddress = false;
                    $address->setUpdatedAt(new \DateTimeImmutable());
                }
                if ('' != $nbStreet) {
                    $address->setNbStreet($nbStreet);
                }
                if ('' != $street) {
                    $address->setStreet($street);
                }
                if ('' != $city) {
                    $address->setCity($city);
                }
                if ('' != $zipCode) {
                    $address->setZipCode($zipCode);
                }
                if ($newAddress) {
                    $company->setAddress($address);
                }
                $entityManager->persist($address);
            }

            if ('' != $employeeNumber) {
                $company->setEmployeeNumber($employeeNumber);
            }

            $company->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($company);
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/company/edit.html.twig', [
            'company' => $company,
            'form' => $form->createView(),
            'connectedPerson' => $personne,
            'address' => $address,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$company->getId(), $request->request->get('_token'))) {
            $entityManager->remove($company);
            $entityManager->flush();
        }

        return $this->redirectToRoute('super_admin_app_company_index', [], Response::HTTP_SEE_OTHER);
    }
}
