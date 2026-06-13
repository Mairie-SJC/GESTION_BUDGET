<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// On verrouille TOUT le contrôleur pour que seuls les Responsables et Admins y aient accès
#[IsGranted('ROLE_RESPONSABLE')]
#[Route('/utilisateurs')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // Affiche la liste de tous les agents enregistrés
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/nouveau', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // 1. On récupère le mot de passe en clair depuis le formulaire
            $motDePasseEnClair = $form->get('plainPassword')->getData();

            // 2. On le hache avec l'algorithme de Symfony
            $motDePasseHache = $passwordHasher->hashPassword(
                $user,
                $motDePasseEnClair
            );
            
            // 3. On attribue le mot de passe haché à l'utilisateur
            $user->setPassword($motDePasseHache);

            // 4. Sauvegarde en base
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', '✅ Le nouveau compte agent a été créé avec succès.');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}