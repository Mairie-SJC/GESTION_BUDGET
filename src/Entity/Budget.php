<?php

namespace App\Entity;

use App\Repository\BudgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
#[ORM\Table(name: 'budgets')]
class Budget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $annee = null;

    #[ORM\Column(length: 10)]
    private ?string $codeComptable = null;

    #[ORM\Column(length: 150)]
    private ?string $libelleLigne = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $montantAlloue = null;

    /**
     * @var Collection<int, Facture>
     */
    #[ORM\OneToMany(targetEntity: Facture::class, mappedBy: 'budget')]
    private Collection $factures;

    public function __construct()
    {
        $this->factures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getCodeComptable(): ?string
    {
        return $this->codeComptable;
    }

    public function setCodeComptable(string $codeComptable): static
    {
        $this->codeComptable = $codeComptable;

        return $this;
    }

    public function getLibelleLigne(): ?string
    {
        return $this->libelleLigne;
    }

    public function setLibelleLigne(string $libelleLigne): static
    {
        $this->libelleLigne = $libelleLigne;

        return $this;
    }

    public function getMontantAlloue(): ?string
    {
        return $this->montantAlloue;
    }

    public function setMontantAlloue(string $montantAlloue): static
    {
        $this->montantAlloue = $montantAlloue;

        return $this;
    }

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): static
    {
        if (!$this->factures->contains($facture)) {
            $this->factures->add($facture);
            $facture->setBudget($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): static
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getBudget() === $this) {
                $facture->setBudget(null);
            }
        }

        return $this;
    }
    /**
     * Calcule le montant total des factures liées à ce budget
     */
    /**
     * Calcule le montant total des factures VALIDÉES liées à ce budget
     */
    public function getMontantConsomme(): float
    {
        $total = 0;
        foreach ($this->getFactures() as $facture) {
            // On ne compte que les factures dont le statut est 'Validée'
            if ($facture->getStatut() === 'Paye') {
                $total += (float)$facture->getMontantTtc();
            }
        }
        return $total;
    }

    /**
     * Calcule le solde restant
     */
    public function getSoldeRestant(): float
    {
        return (float)$this->getMontantAlloue() - $this->getMontantConsomme();
    }
}
