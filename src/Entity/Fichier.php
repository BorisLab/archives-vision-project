<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use ORM\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\Horodateur;
use App\Repository\FichierRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: FichierRepository::class)]
#[UniqueEntity('libelle_fichier')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(columns: ['libelle_fichier'], name: 'idx_fichier_libelle')]
#[ORM\Index(columns: ['type'], name: 'idx_fichier_type')]
#[ORM\Index(columns: ['format'], name: 'idx_fichier_format')]
#[ORM\Index(columns: ['date_creation'], name: 'idx_fichier_date_creation')]
#[ORM\Index(columns: ['dossier_id'], name: 'idx_fichier_dossier')]
#[ORM\Index(columns: ['statut'], name: 'idx_fichier_statut')]
#[ORM\Index(columns: ['tags'], name: 'idx_fichier_tags')]
#[ORM\Index(columns: ['date_debut'], name: 'idx_fichier_date_debut')]
#[ORM\Index(columns: ['date_fin'], name: 'idx_fichier_date_fin')]
#[ORM\Index(columns: ['typologie_documentaire'], name: 'idx_fichier_typologie')]
#[ORM\Index(columns: ['boite_physique_id'], name: 'idx_fichier_boite')]
class Fichier
{

    use Horodateur;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle_fichier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $chemin_acces = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tags = null;

    #[ORM\ManyToOne(inversedBy: 'fichiers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dossier $dossier = null;

    #[ORM\Column(length: 255)]
    private ?string $format = null;

    #[ORM\Column]
    private ?bool $statut = true;

    #[ORM\OneToMany(mappedBy: 'fichier', targetEntity: DemandeAcces::class)]
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

    #[ORM\ManyToOne(targetEntity: BoitePhysique::class, inversedBy: 'fichiers')]
    #[ORM\JoinColumn(nullable: true)]
    private ?BoitePhysique $boite_physique = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $checksum_sha256 = null;

    public function __construct()
    {
        $this->demandeAcces = new ArrayCollection();
    }

    public function getFichierId(): ?int
    {
        return $this->id;
    }

    public function getLibelleFichier(): ?string
    {
        return $this->libelle_fichier;
    }

    public function setLibelleFichier(string $libelle_fichier): static
    {
        $this->libelle_fichier = $libelle_fichier;

        return $this;
    }

    public function getCheminAcces(): ?string
    {
        return $this->chemin_acces;
    }

    public function setCheminAcces(string $chemin_acces): static
    {
        $this->chemin_acces = $chemin_acces;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getDossier(): ?Dossier
    {
        return $this->dossier;
    }

    public function setDossier(?Dossier $dossier): static
    {
        $this->dossier = $dossier;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getExtension(): ?string
    {
        // Vérifier d'abord si le fichier est de type numérique
        if ($this->getFormat() === 'Physique') {
            return ''; // Les fichiers physiques n'ont pas d'extensions
        }

        // Extraire l'extension si le fichier est numérique
        $filePath = $this->getCheminAcces(); // Assure-toi que cette méthode existe pour récupérer le chemin complet
        return pathinfo($filePath, PATHINFO_EXTENSION);
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
            $demandeAcces->setFichier($this);
        }

        return $this;
    }

    public function removeDemandeAcces(DemandeAcces $demandeAcces): static
    {
        if ($this->demandeAcces->removeElement($demandeAcces)) {
            // set the owning side to null (unless already changed)
            if ($demandeAcces->getFichier() === $this) {
                $demandeAcces->setFichier(null);
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
     * Vérifie si le fichier est éligible à la destruction
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
     * Calcule la date à partir de laquelle le fichier peut être détruit
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

    public function getBoitePhysique(): ?BoitePhysique
    {
        return $this->boite_physique;
    }

    public function setBoitePhysique(?BoitePhysique $boite_physique): static
    {
        $this->boite_physique = $boite_physique;
        return $this;
    }

    public function getChecksumSha256(): ?string
    {
        return $this->checksum_sha256;
    }

    public function setChecksumSha256(?string $checksum_sha256): static
    {
        $this->checksum_sha256 = $checksum_sha256;
        return $this;
    }
}
