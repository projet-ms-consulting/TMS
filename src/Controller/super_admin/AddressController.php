<?php

namespace App\Controller\super_admin;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'super_admin_address_')]
class AddressController extends AbstractController
{
    #[Route('super_admin/address/index', name: 'index', methods: ['GET'])]
    public function index(AddressRepository $addressRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 8);
        $sort = $request->query->get('sort', 'a.id');
        $direction = $request->query->get('direction', 'asc');
        $addresses = $addressRepository->paginateAddresses($page, $limit);
        $user = $this->getUser();
        $personne = $user->getPerson();

        return $this->render('super_admin/address/index.html.twig', [
            'addresses' => $addresses,
            'page' => $page,
            'limit' => $limit,
            'sort' => $sort,
            'direction' => $direction,
            'person' => $personne,
        ]);
    }

    #[Route('super_admin/address/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($address);
            $entityManager->flush();
            $this->addFlash('success', 'Adresse '.$address->getFullAddress().' crée avec succes!');

            return $this->redirectToRoute('super_admin_address_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/address/new.html.twig', [
            'address' => $address,
            'form' => $form,
        ]);
    }

    #[Route('super_admin/address/show/{id}', name: 'show', methods: ['GET'])]
    public function show(Address $address): Response
    {
        return $this->render('super_admin/address/show.html.twig', [
            'address' => $address,
        ]);
    }

    #[Route('super_admin/address/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Address $address, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Adresse '.$address->getFullAddress().' modifié avec succes!');

            return $this->redirectToRoute('super_admin_address_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/address/edit.html.twig', [
            'address' => $address,
            'form' => $form,
        ]);
    }

    #[Route('super_admin/address/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Address $address, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$address->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($address);
            $entityManager->flush();
        }
        $this->addFlash('success', 'Adresse '.$address->getFullAddress().' supprimé avec succes!');

        return $this->redirectToRoute('super_admin_address_index', [], Response::HTTP_SEE_OTHER);
    }
}
