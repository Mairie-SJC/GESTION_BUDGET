<?php

namespace App\Controller;

use App\Repository\ContratLicenceRepository;
use App\Repository\FournisseurRepository;
use App\Repository\BudgetRepository;
use App\Repository\FactureRepository;
use App\Entity\Facture;
use App\Form\FactureType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
// use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use setasign\Fpdi\Fpdi;


class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    // Attention ici : vérifiez que le nom de votre repository correspond bien à votre entité (ex: ContratsLicencesRepository ou ContratRepository)
    public function index(BudgetRepository $budgetRepository, FactureRepository $factureRepository, ContratLicenceRepository $contratsRepository): Response
    {
        // 1. La gestion de l'année pour les budgets
        $anneeEnCours = (int) date('Y');
        $budgets = $budgetRepository->findBy(['annee' => $anneeEnCours]);

        // 2. On récupère le reste des données
        $factures = $factureRepository->findAll();
        $contrats = $contratsRepository->findAll(); // <-- La ligne qui manquait !

        // 3. On envoie TOUT à la vue Twig
        return $this->render('dashboard/index.html.twig', [
            'budgets' => $budgets,
            'factures' => $factures,
            'contrats' => $contrats, // <-- On n'oublie pas de la passer ici !
            'annee' => $anneeEnCours,
        ]);
    }

    #[Route('/facture/nouvelle', name: 'app_facture_nouvelle')]
    public function nouvelleFacture(Request $request, EntityManagerInterface $entityManager, FournisseurRepository $fournisseurRepository): Response
    {
        $facture = new Facture();
        $form = $this->createForm(FactureType::class, $facture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            /** @var UploadedFile $pdfFile */
            $pdfFile = $form->get('pdfFile')->getData();

            if ($pdfFile) {
                // 1. Sauvegarde du fichier
                $nouveauNomFichier = uniqid().'.'.$pdfFile->guessExtension();
                $pdfFile->move($this->getParameter('factures_directory'), $nouveauNomFichier);
                $facture->setPdfFilename($nouveauNomFichier);

                // 2. Extraction du texte via le Parser
                $parser = new \Smalot\PdfParser\Parser();
                $cheminFichier = $this->getParameter('factures_directory') . '/' . $nouveauNomFichier;
                $pdfDuFournisseur = $parser->parseFile($cheminFichier);
                $texteBrut = $pdfDuFournisseur->getText();

                // 3. APPLICATION DU TAMPON NUMÉRIQUE (FPDI & FPDF)
                try {
                    $pdf = new Fpdi();
                    $nombrePages = $pdf->setSourceFile($cheminFichier);
                    
                    for ($i = 1; $i <= $nombrePages; $i++) {
                        $templateId = $pdf->importPage($i);
                        $size = $pdf->getTemplateSize($templateId);
                        
                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $pdf->useTemplate($templateId);
                        
                        if ($i === 1) {
                            $pdf->SetFont('Helvetica', 'B', 9);
                            $pdf->SetTextColor(231, 76, 60); // Rouge Mairie
                            $pdf->SetXY($size['width'] - 85, 10);
                            
                            $pdf->Cell(75, 15, '', 1, 0, 'C'); 
                            
                            $pdf->SetXY($size['width'] - 85, 11);
                            $pdf->Cell(75, 5, utf8_decode('MAIRIE DE ST-JUST-EN-CHAUSSÉE'), 0, 1, 'C');
                            $pdf->SetXY($size['width'] - 85, 15);
                            $pdf->Cell(75, 5, utf8_decode('VALIDÉ POUR PAIEMENT'), 0, 1, 'C');
                            $pdf->SetXY($size['width'] - 85, 19);
                            $pdf->Cell(75, 5, 'Le ' . date('d/m/Y') . ' - Service Info', 0, 1, 'C');
                        }
                    }
                    
                    $pdf->Output($cheminFichier, 'F');
                } catch (\Exception $e) {
                    // Ignoré si PDF protégé
                }

                // 4. Extraction Multi-Données Optimisée
                $textePropre = preg_replace('/\s+/u', ' ', $texteBrut);
                $messageIA = '';

                // A. DÉTECTION DU MONTANT TTC
                $montantTrouve = null;
                if (preg_match('/ttc[^\d]+([0-9]+[.,][0-9]{2})/i', $textePropre, $matches)) {
                    $montantTrouve = str_replace(',', '.', $matches[1]);
                    $facture->setMontantTtc((float)$montantTrouve);
                    $messageIA .= ' 💰 Montant : ' . $montantTrouve . ' € |';
                }

                // B. DÉTECTION DE LA DATE (La découpe chirurgicale adaptée à l'entité)
                if (preg_match('/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/', $textePropre, $matches)) {
                    $jour = $matches[1];
                    $mois = $matches[2];
                    $annee = $matches[3];

                    $datePropre = "$annee-$mois-$jour";

                    try {
                        // 1. On utilise \DateTime normal (ce que veut votre entité)
                        $dateObj = new \DateTime($datePropre);
                        
                        // 2. On utilise le VRAI nom de votre fonction !
                        $facture->setDateFacture($dateObj);
                        
                        $messageIA .= ' 📅 Date : ' . "$jour/$mois/$annee" . ' |';
                    } catch (\Exception $e) {
                        // Sécurité anti-crash
                    }
                }

                // C. DÉTECTION DU NUMÉRO DE FACTURE
                if (preg_match('/Facture\s*n°\s*([A-Z0-9-_]+)/i', $textePropre, $matches)) {
                    $numeroTrouve = trim($matches[1]);
                    $facture->setNumeroFacture($numeroTrouve);
                    $messageIA .= ' 🔢 N° : ' . $numeroTrouve . ' |';
                }

                // D. RECONNAISSANCE AUTOMATIQUE DU FOURNISSEUR
                // On récupère tous les fournisseurs enregistrés en base
                $fournisseurs = $fournisseurRepository->findAll();
                
                foreach ($fournisseurs as $fournisseur) {
                    // On vérifie si le nom de l'entreprise est présent dans le texte du PDF (insensible à la casse)
                    if (stripos($textePropre, $fournisseur->getNomEntreprise()) !== false) {
                        $facture->setFournisseur($fournisseur);
                        $messageIA .= ' 🏢 Fournisseur : ' . $fournisseur->getNomEntreprise() . ' |';
                        break; // Dès qu'on trouve le fournisseur, on arrête la boucle
                    }
                }

                // Synthèse du message flash
                if ($messageIA !== '') {
                    $messageIA = ' 🤖 Extraction IA réussie : ' . rtrim($messageIA, ' |');
                } else {
                    $messageIA = ' 🤖 L\'IA n\'a rien pu extraire.';
                }
            } // Fin du bloc if ($pdfFile)

            // 5. Sauvegarde officielle dans la base de données
            $entityManager->persist($facture);
            $entityManager->flush();

            // Envoi du message flash combiné avec style forcé en noir
            $messageFinal = '<span style="color: black; font-weight: 500;">✅ Facture et PDF enregistrés !' . ($messageIA ?? '') . '</span>';

            $this->addFlash('success', $messageFinal);

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('dashboard/nouvelle_facture.html.twig', [
            'formulaire' => $form->createView(),
        ]);
    }

    #[Route('/facture/modifier/{id}', name: 'app_facture_modifier')]
    public function modifierFacture(\App\Entity\Facture $facture, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(\App\Form\FactureType::class, $facture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            /** @var UploadedFile $pdfFile */
            $pdfFile = $form->get('pdfFile')->getData();
            if ($pdfFile) {
                $nouveauNomFichier = uniqid().'.'.$pdfFile->guessExtension();
                $pdfFile->move($this->getParameter('factures_directory'), $nouveauNomFichier);
                $facture->setPdfFilename($nouveauNomFichier);
            }

            $entityManager->flush(); 
            $this->addFlash('success', '✅ La facture a bien été modifiée.');
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('dashboard/editer_facture.html.twig', [
            'formulaire' => $form->createView(),
            'facture' => $facture,
        ]);
    }

    #[Route('/facture/supprimer/{id}', name: 'app_facture_supprimer')]
    public function supprimerFacture(\App\Entity\Facture $facture, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($facture);
        $entityManager->flush();
        $this->addFlash('success', '🗑️ La facture a bien été supprimée de la base.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/facture/{id}/valider', name: 'app_facture_valider', methods: ['POST'])]
    public function valider(Facture $facture, EntityManagerInterface $entityManager): Response
    {
        // Bascule directe du statut
        $facture->setStatut('Paye'); 
        
        // Sauvegarde en base de données
        $entityManager->flush();

        // Affichage du message de succès avec votre mise en forme (texte noir)
        $this->addFlash('success', '<span style="color: black; font-weight: 500;">✅ Facture validée (Passage en statut "Paye"). Le budget a été mis à jour.</span>');

        // Retour immédiat au tableau de bord
        return $this->redirectToRoute('app_dashboard');
    }
}