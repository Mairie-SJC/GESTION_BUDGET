<?php

namespace App\Form;

use App\Entity\Fournisseur;
use App\Entity\Budget;
use App\Entity\ContratLicence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContratLicenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelleService', null, [
                'label' => 'Nom du service / Logiciel'
            ])
            // --- LA CORRECTION DU FOURNISSEUR ---
            ->add('fournisseur', EntityType::class, [
                'class' => Fournisseur::class,
                'label' => 'Fournisseur'
                // Pas besoin de 'choice_label' grâce à notre fonction magique !
            ])
            // ------------------------------------
            ->add('montantAnnuel', null, [
                'label' => 'Montant annuel HT'
            ])
            ->add('dateDebut', null, [
                'widget' => 'single_text', // Affiche un beau calendrier cliquable
                'label' => 'Date de début'
            ])
            ->add('dateFin', null, [
                'widget' => 'single_text',
                'required' => false, // Optionnel, s'il n'y a pas de date de fin prévue
                'label' => 'Date de fin'
            ])
            ->add('datePreavis', null, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de préavis (alerte)'
            ])
            ->add('statut')
            // --- LA CORRECTION DU BUDGET ---
            ->add('budget', EntityType::class, [
                'class' => Budget::class,
                'choice_label' => 'libelleLigne', // On affiche le nom en clair !
                'label' => 'Ligne budgétaire'
            ])
            // -------------------------------
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContratLicence::class,
        ]);
    }
}