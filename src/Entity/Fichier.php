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
class Fichier
{

    use Horodateur;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle_fichier = null;

    #[ORM\Column(length: 255)]
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

    public function removeDemandeAcces(DemandeAcces $demandeAcce): static
    {
        if ($this->demandeAcces->removeElement($demandeAcces)) {
            // set the owning side to null (unless already changed)
            if ($demandeAcces->getFichier() === $this) {
                $demandeAcces->setFichier(null);
            }
        }

        return $this;
    }
}
