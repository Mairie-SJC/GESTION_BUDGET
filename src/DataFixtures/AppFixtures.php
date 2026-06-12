<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    // On injecte le service de hachage de mot de passe
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Création de VOTRE compte (Administrateur / Comptable pour tester les boutons)
        $admin = new User();
        $admin->setEmail('admin@sjc.fr');
        // On vous donne les droits maximums pour le développement
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_COMPTABLE', 'ROLE_MAIRE']); 
        
        // Hachage du mot de passe "sjc2026"
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'sjc2026');
        $admin->setPassword($hashedPassword);
        
        $manager->persist($admin);

        // 2. Création d'un compte Agent standard (pour tester plus tard)
        $agent = new User();
        $agent->setEmail('agent@sjc.fr');
        $agent->setRoles(['ROLE_USER']); // Rôle de base
        
        $agentPassword = $this->passwordHasher->hashPassword($agent, 'agent123');
        $agent->setPassword($agentPassword);
        
        $manager->persist($agent);

        // On envoie tout dans la base de données
        $manager->flush();
    }
}