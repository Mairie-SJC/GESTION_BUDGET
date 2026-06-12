<?php

namespace App\Controller;

use App\Entity\ContratLicence;
use App\Form\ContratLicenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route; // On garde bien l'attribut PHP 8 !

class ContratController extends AbstractController
{
    #[Route('/contrats', name: 'app_contrat')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contrat = new ContratLicence();
        $form = $this->createForm(ContratLicenceType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contrat);
            $entityManager->flush();

            $this->addFlash('success', '✅ Le contrat/licence a bien été ajouté !');
            return $this->redirectToRoute('app_contrat');
        }

        $contrats = $entityManager->getRepository(ContratLicence::class)->findAll();

        return $this->render('contrat/index.html.twig', [
            'form' => $form->createView(),
            'contrats' => $contrats,
        ]);
    }

    #[Route('/contrats/{id}/modifier', name: 'app_contrat_edit')]
    public function edit(Request $request, ContratLicence $contrat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContratLicenceType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Pas besoin de "persist" car la base connaît déjà ce contrat
            $this->addFlash('success', '✏️ Le contrat a bien été modifié !');
            return $this->redirectToRoute('app_contrat');
        }

        return $this->render('contrat/edit.html.twig', [
            'contrat' => $contrat,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/contrats/{id}/supprimer', name: 'app_contrat_delete', methods: ['POST'])]
    public function delete(Request $request, ContratLicence $contrat, EntityManagerInterface $entityManager): Response
    {
        // Sécurité pour éviter qu'on supprime un contrat en tapant juste l'adresse
        if ($this->isCsrfTokenValid('delete'.$contrat->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contrat);
            $entityManager->flush();
            $this->addFlash('success', '🗑️ Le contrat a été supprimé.');
        }

        return $this->redirectToRoute('app_contrat');
    }
}