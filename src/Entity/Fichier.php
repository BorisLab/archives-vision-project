<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use ORM\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\Horodateur;
use App\Repository\FichierRepository;

#[ORM\Entity(repositoryClass: FichierRepository::class)]
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

    public function getId(): ?int
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

    public function getDossier(): ?Dossier
    {
        return $this->dossier;
    }

    public function setDossier(?Dossier $dossier): static
    {
        $this->dossier = $dossier;

        return $this;
    }
}
