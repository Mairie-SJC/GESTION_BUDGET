<?php

namespace App\Controller;

use App\Entity\ContratLicence;
use App\Form\ContratLicenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContratController extends AbstractController
{
    #[Route('/contrats', name: 'app_contrat')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. Préparation du formulaire pour un NOUVEAU contrat
        $contrat = new ContratLicence();
        $form = $this->createForm(ContratLicenceType::class, $contrat);
        $form->handleRequest($request);

        // Si le formulaire est envoyé et valide, on sauvegarde dans MariaDB
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contrat);
            $entityManager->flush();

            $this->addFlash('success', '✅ Le contrat/licence a bien été ajouté !');
            return $this->redirectToRoute('app_contrat'); // On recharge la page proprement
        }

        // 2. Récupération de la liste des contrats existants
        $contrats = $entityManager->getRepository(ContratLicence::class)->findAll();

        // 3. Envoi de tout ça à la page Twig (la Vue)
        return $this->render('contrat/index.html.twig', [
            'form' => $form->createView(),
            'contrats' => $contrats,
        ]);
    }
}