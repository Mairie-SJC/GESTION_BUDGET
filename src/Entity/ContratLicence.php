<?php

namespace App\Entity;

use App\Repository\ContratLicenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM; // <-- Cette ligne doit être présente
use App\Entity\Fournisseur;

#[ORM\Entity(repositoryClass: ContratLicenceRepository::class)] // <-- NE PAS OUBLIER CETTE LIGNE
#[ORM\Table(name: 'contrats_licences')] // <-- Celle que nous avons ajoutée
class ContratLicence
{
    #[ORM\ManyToOne(targetEntity: Fournisseur::class)]
    #[ORM\JoinColumn(name: 'fournisseur_id', nullable: false)]
    private ?Fournisseur $fournisseur = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // --- DEBUT DU BLOC A AJOUTER ---
    #[ORM\Column(name: 'libelle_service', length: 150)]
    private ?string $libelleService = null;

    #[ORM\ManyToOne(targetEntity: Budget::class)]
    #[ORM\JoinColumn(name: 'budget_id', nullable: false)]
    private ?Budget $budget = null;
    // --- FIN DU BLOC A AJOUTER ---

    #[ORM\Column(name: 'montant_annuel_ht', type: Types::DECIMAL, precision: 10, scale: 2)] // <- Attention au tiret du bas !
    private ?string $montantAnnuel = null;

    #[ORM\Column(name: 'date_debut', type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: 'date_fin', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(name: 'date_preavis', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datePreavis = null;

    // ... (laissez statut tranquille)

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelleService(): ?string
    {
        return $this->libelleService;
    }

    public function setLibelleService(string $libelleService): static
    {
        $this->libelleService = $libelleService;

        return $this;
    }

    
    public function getFournisseur(): ?Fournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseur $fournisseur): static
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): static
    {
        $this->budget = $budget;

        return $this;
    }

    public function getMontantAnnuel(): ?string
    {
        return $this->montantAnnuel;
    }

    public function setMontantAnnuel(string $montantAnnuel): static
    {
        $this->montantAnnuel = $montantAnnuel;

        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getDatePreavis(): ?\DateTime
    {
        return $this->datePreavis;
    }

    public function setDatePreavis(?\DateTime $datePreavis): static
    {
        $this->datePreavis = $datePreavis;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
}
