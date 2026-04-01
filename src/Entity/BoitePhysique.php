<?php

namespace App\Entity;

use App\Entity\Traits\Horodateur;
use App\Repository\BoitePhysiqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BoitePhysiqueRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('code_boite')]
class BoitePhysique
{
    use Horodateur;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le code de la boîte est obligatoire')]
    #[Assert\Length(
        max: 50,
        maxMessage: 'Le code ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $code_boite = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le libellé de la boîte est obligatoire')]
    private ?string $libelle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $localisation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'La capacité doit être un nombre positif')]
    private ?int $capacite_max = null;

    #[ORM\Column]
    private ?bool $statut = true;

    #[ORM\OneToMany(mappedBy: 'boite_physique', targetEntity: Fichier::class)]
    private Collection $fichiers;

    public function __construct()
    {
        $this->fichiers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeBoite(): ?string
    {
        return $this->code_boite;
    }

    public function setCodeBoite(string $code_boite): static
    {
        $this->code_boite = $code_boite;
        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): static
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCapaciteMax(): ?int
    {
        return $this->capacite_max;
    }

    public function setCapaciteMax(?int $capacite_max): static
    {
        $this->capacite_max = $capacite_max;
        return $this;
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
            $fichier->setBoitePhysique($this);
        }
        return $this;
    }

    public function removeFichier(Fichier $fichier): static
    {
        if ($this->fichiers->removeElement($fichier)) {
            if ($fichier->getBoitePhysique() === $this) {
                $fichier->setBoitePhysique(null);
            }
        }
        return $this;
    }

    /**
     * Calcule le nombre de fichiers actuellement dans la boîte
     */
    public function getNombreFichiers(): int
    {
        return $this->fichiers->count();
    }

    /**
     * Vérifie si la boîte a atteint sa capacité maximale
     */
    public function estPleine(): bool
    {
        if ($this->capacite_max === null) {
            return false;
        }
        return $this->getNombreFichiers() >= $this->capacite_max;
    }

    /**
     * Calcule le taux de remplissage en pourcentage
     */
    public function getTauxRemplissage(): ?float
    {
        if ($this->capacite_max === null || $this->capacite_max === 0) {
            return null;
        }
        return ($this->getNombreFichiers() / $this->capacite_max) * 100;
    }

    public function __toString(): string
    {
        return $this->code_boite . ' - ' . $this->libelle;
    }
}
