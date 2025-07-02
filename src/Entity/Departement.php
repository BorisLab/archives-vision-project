<?php

namespace App\Entity;

use App\Entity\Utilisateur;
use ORM\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\Horodateur;
use App\Repository\DepartementRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: DepartementRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Departement
{

    use Horodateur;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle_dep = null;

    #[ORM\Column]
    private ?bool $parent = null;

    #[ORM\OneToMany(mappedBy: 'departement', targetEntity: Utilisateur::class)]
    private Collection $utilisateurs;

    #[ORM\OneToMany(mappedBy: 'departement', targetEntity: Dossier::class)]
    private Collection $dossiers;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'departements')]
    private ?self $departement_parent = null;

    #[ORM\OneToMany(mappedBy: 'departement_parent', targetEntity: self::class)]
    private Collection $departements;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
        $this->dossiers = new ArrayCollection();
        $this->departements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelleDep(): ?string
    {
        return $this->libelle_dep;
    }

    public function setLibelleDep(string $libelle_dep): self
    {
        $this->libelle_dep = $libelle_dep;

        return $this;
    }

    public function isParent(): ?bool
    {
        return $this->parent;
    }

    public function setParent(bool $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->setDepartement($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getDepartement() === $this) {
                $utilisateur->setDepartement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Dossier>
     */
    public function getDossiers(): Collection
    {
        return $this->dossiers;
    }

    public function addDossier(Dossier $dossier): static
    {
        if (!$this->dossiers->contains($dossier)) {
            $this->dossiers->add($dossier);
            $dossier->setDepartement($this);
        }

        return $this;
    }

    public function removeDossier(Dossier $dossier): static
    {
        if ($this->dossiers->removeElement($dossier)) {
            // set the owning side to null (unless already changed)
            if ($dossier->getDepartement() === $this) {
                $dossier->setDepartement(null);
            }
        }

        return $this;
    }

    public function getDepartementParent(): ?self
    {
        return $this->departement_parent;
    }

    public function setDepartementParent(?self $departement_parent): static
    {
        $this->departement_parent = $departement_parent;

        return $this;
    }

    public function getDepartementRacine(): self
    {
        $departement_racine = $this;
        while ($departement_racine->getDepartementParent() !== null) {
            $departement_racine = $departement_racine->getDepartementParent();
        }
        return $departement_racine;
    }

    /**
     * @return Collection<int, self>
     */
    public function getDepartements(): Collection
    {
        return $this->departements;
    }

    public function addDepartement(self $departement): static
    {
        if (!$this->departements->contains($departement)) {
            $this->departements->add($departement);
            $departement->setDepartementParent($this);
        }

        return $this;
    }

    public function removeDepartement(self $departement): static
    {
        if ($this->departements->removeElement($departement)) {
            // set the owning side to null (unless already changed)
            if ($departement->getDepartementParent() === $this) {
                $departement->setDepartementParent(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function persistParent()
    {
        $this->parent = $this->departement_parent !== null;
    }

    public function estDansDepartementOuSousDepartement(Utilisateur $user): bool
    {
        $departementActuel = $user->getDepartement();
    
        while ($departementActuel !== null) {
            if ($departementActuel === $this) {
                return true;
            }
            $departementActuel = $departementActuel->getDepartementParent();
        }
    
        return false;
    }
    

}
