<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail de l\'agent',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Niveau d\'accès',
                'choices' => [
                    'Agent (Consultation)' => 'ROLE_USER',
                    'Responsable (Ajout/Validation)' => 'ROLE_RESPONSABLE',
                    'Administrateur (Contrôle total)' => 'ROLE_ADMIN',
                ],
                'multiple' => true, // Symfony stocke les rôles dans un tableau (array)
                'expanded' => true, // Affiche des cases à cocher plutôt qu'un menu déroulant
            ])
            // On ne lie pas directement le mot de passe à l'entité car il doit être haché avant !
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe provisoire',
                'mapped' => false, 
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}