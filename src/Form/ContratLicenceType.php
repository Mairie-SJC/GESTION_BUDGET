<?php

namespace App\Form;

use App\Entity\ContratLicence;
use App\Entity\Fournisseur;
use App\Entity\Budget;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ContratLicenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelleService', TextType::class, [
                'label' => 'Nom de la Licence / Service'
            ])
            ->add('montantAnnuel', null, [
                'label' => 'Montant Annuel (HT)'
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de Début'
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de Fin'
            ])
            ->add('datePreavis', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de Préavis'
            ])
            ->add('statut', TextType::class, [
                'required' => false,
                'label' => 'Statut (Actif, Résilié...)'
            ])
            
            // --- LISTE DEROULANTE DES FOURNISSEURS ---
            ->add('fournisseur', EntityType::class, [
                'class' => Fournisseur::class,
                'choice_label' => 'nomEntreprise', // Utilise la propriété exacte vue dans DBeaver
                'label' => 'Fournisseur Prestataire'
            ])
            
            // --- LISTE DEROULANTE DES BUDGETS ---
            ->add('budget', EntityType::class, [
                'class' => Budget::class,
                'choice_label' => 'codeComptable', // Affichera le numéro de compte (ex: 611)
                'label' => 'Compte d\'Imputation Budgétaire'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContratLicence::class,
        ]);
    }
}