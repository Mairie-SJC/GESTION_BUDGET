<?php

namespace App\Form;

use App\Entity\Fournisseur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Entity\Budget;
use App\Entity\Facture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class FactureType extends AbstractType
{
        public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroFacture')
            ->add('dateFacture', null, [
                'widget' => 'single_text', // Permet d'afficher un beau calendrier HTML5
            ])
            ->add('montantTtc')
            ->add('statut', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices'  => [
                // 'Ce que l'utilisateur voit' => 'La valeur ENUM exacte pour MariaDB'
                    'À valider'                => 'A valider',
                    'Transmis à la compta'     => 'Transmis Compta',
                    'En attente de paiement'   => 'En attente de paiement',
                    'Payé'                     => 'Paye',
                ],
                'placeholder' => 'Sélectionnez le statut d\'avancement',
            ])
            ->add('fournisseur', EntityType::class, [
                'class' => Fournisseur::class,
                'choice_label' => 'nomEntreprise', // <-- Avec un E majuscule et sans tiret
                'placeholder' => 'Choisissez un fournisseur...',
            ])
            ->add('pdfFile', FileType::class, [
                'label' => 'Facture au format PDF',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: [
                            'application/pdf',
                        ],
                        mimeTypesMessage: 'Veuillez téléverser un document PDF valide'
                    )
                ],
            ])
            
            ->add('budget', null, [
                'choice_label' => 'codeComptable', // Affiche le code comptable dans le menu déroulant plutôt qu'un identifiant technique
            ])
        ;
    }
}
