<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use ORM\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\Horodateur;
use App\Repository\DossierRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: DossierRepository::class)]
#[UniqueEntity('libelle_dossier')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(columns: ['libelle_dossier'], name: 'idx_dossier_libelle')]
#[ORM\Index(columns: ['tags'], name: 'idx_dossier_tags')]
#[ORM\Index(columns: ['date_creation'], name: 'idx_dossier_date_creation')]
#[ORM\Index(columns: ['departement_id'], name: 'idx_dossier_departement')]
#[ORM\Index(columns: ['format'], name: 'idx_dossier_format')]
#[ORM\Index(columns: ['statut'], name: 'idx_dossier_statut')]
#[ORM\Index(columns: ['date_debut'], name: 'idx_dossier_date_debut')]
#[ORM\Index(columns: ['date_fin'], name: 'idx_dossier_date_fin')]
#[ORM\Index(columns: ['typologie_documentaire'], name: 'idx_dossier_typologie')]
#[ORM\Index(columns: ['utilisateur_id'], name: 'idx_dossier_utilisateur')]
class Dossier
{

    use Horodateur;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle_dossier = null;

    #[ORM\Column]
    private ?string $format = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tags = null;

    #[ORM\Column]
    private ?bool $parent = null;

    #[ORM\ManyToOne(inversedBy: 'dossiers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Departement $departement = null;

    #[ORM\ManyToOne(inversedBy: 'dossiers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'dossier', targetEntity: Fichier::class)]
    private Collection $fichiers;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'dossiers')]
    private ?self $dossier_parent = null;

    #[ORM\OneToMany(mappedBy: 'dossier_parent', targetEntity: self::class)]
    private Collection $dossiers;

    #[ORM\Column]
    private ?bool $statut = true;

    #[ORM\OneToMany(mappedBy: 'dossier', targetEntity: DemandeAcces::class)]
    private Collection $demandeAcces;

    #[ORM\ManyToOne(targetEntity: RegleRetention::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?RegleRetention $regle_retention = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typologie_documentaire = null;

    public function __construct()
    {
        $this->fichiers = new ArrayCollection();
        $this->dossiers = new ArrayCollection();
        $this->demandeAcces = new ArrayCollection();
    }

    public function getDossierId(): ?int
    {
        return $this->id;
    }

    public function getLibelleDossier(): ?string
    {
        return $this->libelle_dossier;
    }

    public function setLibelleDossier(string $libelle_dossier): static
    {
        $this->libelle_dossier = $libelle_dossier;

        return $this;
    }

    public function getFormat(): ?string
    {
        $format = $this->format;

        return $format;
    }

    public function setFormat(?string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function getFormattedTags(): ?string
    {
        return str_replace(',', ', ', $this->tags);
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

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * @return Collection<int, Fichier>
     */
    public function getFichiers(): Collection
    {
        return $this->fichiers;
    }

    public function addFichier(Fichier $fichier): static
    {
        if (!$this->fichiers->contains($fichier)) {
            $this->fichiers->add($fichier);
            $fichier->setDossier($this);
        }

        return $this;
    }

    public function removeFichier(Fichier $fichier): static
    {
        if ($this->fichiers->removeElement($fichier)) {
            // set the owning side to null (unless already changed)
            if ($fichier->getDossier() === $this) {
                $fichier->setDossier(null);
            }
        }

        return $this;
    }

    public function getDossierParent(): ?self
    {
        return $this->dossier_parent;
    }

    public function setDossierParent(?self $dossier_parent): static
    {
        $this->dossier_parent = $dossier_parent;

        return $this;
    }


    public function getDossierRacine(): self
    {
        $dossier_racine = $this;
        while ($dossier_racine->getDossierParent() !== null) {
            $dossier_racine = $dossier_racine->getDossierParent();
        }
        return $dossier_racine;
    }

    public function getArborescence(): array
    {
        $arbo = [];
        $dossierCourant = $this;

        while ($dossierCourant) {
            array_unshift($arbo, $dossierCourant);
            $dossierCourant = $dossierCourant->getDossierParent();
        }

        return $arbo;
    }

    /**
     * @return Collection<int, self>
     */
    public function getDossiers(): Collection
    {
        return $this->dossiers;
    }

    public function addDossier(self $dossier): static
    {
        if (!$this->dossiers->contains($dossier)) {
            $this->dossiers->add($dossier);
            $dossier->setDossierParent($this);
        }

        return $this;
    }

    public function removeDossier(self $dossier): static
    {
        if ($this->dossiers->removeElement($dossier)) {
            // set the owning side to null (unless already changed)
            if ($dossier->getDossierParent() === $this) {
                $dossier->setDossierParent(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function persistParent()
    {
        $this->parent = $this->dossier_parent !== null;
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, DemandeAcces>
     */
    public function getDemandeAcces(): Collection
    {
        return $this->demandeAcces;
    }

    public function addDemandeAcces(DemandeAcces $demandeAcces): static
    {
        if (!$this->demandeAcces->contains($demandeAcces)) {
            $this->demandeAcces->add($demandeAcces);
            $demandeAcces->setDossier($this);
        }

        return $this;
    }

    public function removeDemandeAcces(DemandeAcces $demandeAcces): static
    {
        if ($this->demandeAcces->removeElement($demandeAcces)) {
            // set the owning side to null (unless already changed)
            if ($demandeAcces->getDossier() === $this) {
                $demandeAcces->setDossier(null);
            }
        }

        return $this;
    }

    public function getRegleRetention(): ?RegleRetention
    {
        return $this->regle_retention;
    }

    public function setRegleRetention(?RegleRetention $regle_retention): static
    {
        $this->regle_retention = $regle_retention;
        return $this;
    }

    /**
     * Vérifie si le dossier est éligible à la destruction
     * basé sur la règle de rétention et la date de création
     */
    public function isEligiblePourDestruction(): bool
    {
        if (!$this->regle_retention) {
            return false;
        }
        
        if (!$this->getCreatedAt()) {
            return false;
        }
        
        $dateDestructionPossible = $this->getDateDestructionPossible();
        return $dateDestructionPossible && new \DateTime() >= $dateDestructionPossible;
    }

    /**
     * Calcule la date à partir de laquelle le dossier peut être détruit
     */
    public function getDateDestructionPossible(): ?\DateTime
    {
        if (!$this->regle_retention || !$this->getCreatedAt()) {
            return null;
        }
        
        $dateCreation = clone $this->getCreatedAt();
        $dureeConservation = $this->regle_retention->getDureeConservation();
        $dateCreation->modify("+{$dureeConservation} years");
        
        return $dateCreation;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(?\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;
        return $this;
    }

    public function getTypologieDocumentaire(): ?string
    {
        return $this->typologie_documentaire;
    }

    public function setTypologieDocumentaire(?string $typologie_documentaire): static
    {
        $this->typologie_documentaire = $typologie_documentaire;
        return $this;
    }
}
