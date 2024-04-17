<?php

namespace App\Controller\admin;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/address', name: 'admin_address_')]
class AddressController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(AddressRepository $addressRepository): Response
    {
        $addresses = $addressRepository->findAll();
        return $this->render('admin/address/index.html.twig', [
            'addresses' => $addresses,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
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
            return $this->redirectToRoute('admin_address_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/address/new.html.twig', [
            'address' => $address,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Address $address): Response
    {
        return $this->render('admin/address/show.html.twig', [
            'address' => $address,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Address $address, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Adresse '.$address->getFullAddress().' modifié avec succes!');
            return $this->redirectToRoute('admin_address_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/address/edit.html.twig', [
            'address' => $address,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Address $address, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$address->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($address);
            $entityManager->flush();
        }
        $this->addFlash('success', 'Adresse '.$address->getFullAddress().' supprimé avec succes!');
        return $this->redirectToRoute('admin_address_index', [], Response::HTTP_SEE_OTHER);
    }
}
