<?php

namespace App\Controller;

use App\Entity\ContratLicence;
use App\Form\ContratLicenceType;
use App\Repository\ContratLicenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contrat/licence')]
final class ContratLicenceController extends AbstractController
{
    #[Route(name: 'app_contrat_licence_index', methods: ['GET'])]
    public function index(ContratLicenceRepository $contratLicenceRepository): Response
    {
        return $this->render('contrat_licence/index.html.twig', [
            'contrat_licences' => $contratLicenceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contrat_licence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contratLicence = new ContratLicence();
        $form = $this->createForm(ContratLicenceType::class, $contratLicence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contratLicence);
            $entityManager->flush();

            return $this->redirectToRoute('app_contrat_licence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contrat_licence/new.html.twig', [
            'contrat_licence' => $contratLicence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contrat_licence_show', methods: ['GET'])]
    public function show(ContratLicence $contratLicence): Response
    {
        return $this->render('contrat_licence/show.html.twig', [
            'contrat_licence' => $contratLicence,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contrat_licence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContratLicence $contratLicence, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContratLicenceType::class, $contratLicence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_contrat_licence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contrat_licence/edit.html.twig', [
            'contrat_licence' => $contratLicence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contrat_licence_delete', methods: ['POST'])]
    public function delete(Request $request, ContratLicence $contratLicence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contratLicence->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($contratLicence);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contrat_licence_index', [], Response::HTTP_SEE_OTHER);
    }
}
