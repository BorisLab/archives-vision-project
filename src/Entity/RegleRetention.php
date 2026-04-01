<?php

namespace App\Entity;

use App\Repository\RegleRetentionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegleRetentionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class RegleRetention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $duree_conservation = null; // Durée en années

    #[ORM\Column(type: Types::TEXT)]
    private ?string $base_legale = null; // Référence légale (loi, décret, etc.)

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created_at = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updated_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDureeConservation(): ?int
    {
        return $this->duree_conservation;
    }

    public function setDureeConservation(int $duree_conservation): static
    {
        $this->duree_conservation = $duree_conservation;
        return $this;
    }

    public function getBaseLegale(): ?string
    {
        return $this->base_legale;
    }

    public function setBaseLegale(string $base_legale): static
    {
        $this->base_legale = $base_legale;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * Calcule si un dossier est éligible à la destruction
     * Basé sur la date de fin du dossier + durée de conservation
     * 
     * @param Dossier $dossier Le dossier à vérifier
     * @return bool True si éligible à la destruction
     */
    public function isEligibleForDestruction(Dossier $dossier): bool
    {
        // Si pas de date de fin ou pas de durée, pas éligible
        if (!$dossier->getDateFin() || !$this->duree_conservation) {
            return false;
        }

        // Calculer la date d'éligibilité = date_fin + durée_conservation
        $dateEligibilite = (clone $dossier->getDateFin())
            ->modify("+{$this->duree_conservation} years");

        // Comparer avec aujourd'hui
        return $dateEligibilite <= new \DateTime();
    }

    /**
     * Calcule la date à laquelle le dossier sera éligible à destruction
     * 
     * @param Dossier $dossier Le dossier concerné
     * @return \DateTimeInterface|null Date d'éligibilité ou null si pas calculable
     */
    public function getDestructionEligibilityDate(Dossier $dossier): ?\DateTimeInterface
    {
        if (!$dossier->getDateFin() || !$this->duree_conservation) {
            return null;
        }

        return (clone $dossier->getDateFin())
            ->modify("+{$this->duree_conservation} years");
    }

    /**
     * Retourne le nombre d'années restantes avant éligibilité destruction
     * 
     * @param Dossier $dossier Le dossier concerné
     * @return int|null Années restantes (peut être négatif si déjà éligible), null si pas calculable
     */
    public function getYearsUntilDestruction(Dossier $dossier): ?int
    {
        $dateEligibilite = $this->getDestructionEligibilityDate($dossier);
        
        if (!$dateEligibilite) {
            return null;
        }

        $now = new \DateTime();
        $interval = $now->diff($dateEligibilite);
        
        // Négatif si déjà éligible
        return $interval->invert ? -$interval->y : $interval->y;
    }

    /**
     * Calcule si un fichier est éligible à la destruction
     * Basé sur la date de création du fichier + durée de conservation
     * 
     * @param Fichier $fichier Le fichier à vérifier
     * @return bool True si éligible à la destruction
     */
    public function isFileEligibleForDestruction(Fichier $fichier): bool
    {
        // Si pas de date de création ou pas de durée, pas éligible
        if (!$fichier->getCreatedAt() || !$this->duree_conservation) {
            return false;
        }

        // Calculer la date d'éligibilité = created_at + durée_conservation
        $dateEligibilite = (clone $fichier->getCreatedAt())
            ->modify("+{$this->duree_conservation} years");

        // Comparer avec aujourd'hui
        return $dateEligibilite <= new \DateTime();
    }

    /**
     * Calcule la date à laquelle le fichier sera éligible à destruction
     * 
     * @param Fichier $fichier Le fichier concerné
     * @return \DateTimeInterface|null Date d'éligibilité ou null si pas calculable
     */
    public function getFileDestructionEligibilityDate(Fichier $fichier): ?\DateTimeInterface
    {
        if (!$fichier->getCreatedAt() || !$this->duree_conservation) {
            return null;
        }

        return (clone $fichier->getCreatedAt())
            ->modify("+{$this->duree_conservation} years");
    }
}
