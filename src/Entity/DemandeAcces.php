<?php

namespace App\Entity;

use App\Entity\StatutDemandeAcces;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use ORM\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\Horodateur;
use App\Repository\DemandeAccesRepository;

#[ORM\Entity(repositoryClass: DemandeAccesRepository::class)]
#[ORM\HasLifecycleCallbacks]
class DemandeAcces
{

    use Horodateur;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string", enumType: StatutDemandeAcces::class, options: ['default' => 'pending'], length: 255)]
    private StatutDemandeAcces $statut;

    #[ORM\ManyToOne(inversedBy: 'demandeAcces')]
    private ?Dossier $dossier = null;

    #[ORM\ManyToOne(inversedBy: 'demandeAcces')]
    private ?Fichier $fichier = null;

    #[ORM\ManyToOne(inversedBy: 'demandeAcces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'demande_acces', targetEntity: Notification::class)]
    private Collection $notifications;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motif_rejet = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiration = null;

    #[ORM\Column(nullable: true)]
    private ?int $archiviste_id = null;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): StatutDemandeAcces
    {
        return $this->statut;
    }

    public function setStatut(StatutDemandeAcces $statut): self
    {
        $this->statut = $statut;

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

    public function getFichier(): ?Fichier
    {
        return $this->fichier;
    }

    public function setFichier(?Fichier $fichier): static
    {
        $this->fichier = $fichier;

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

    public function getArchivisteId(): ?int
    {
        return $this->archiviste_id;
    }

    public function setArchivisteId(?int $archiviste_id): static
    {
        $this->archiviste_id = $archiviste_id;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setDemandeAcces($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getDemandeAcces() === $this) {
                $notification->setDemandeAcces(null);
            }
        }

        return $this;
    }

    public function getMotifRejet(): ?string
    {
        return $this->motif_rejet;
    }

    public function setMotifRejet(?string $motif_rejet): static
    {
        $this->motif_rejet = $motif_rejet;

        return $this;
    }

    public function getExpiration(): ?\DateTimeInterface
    {
        return $this->expiration;
    }

    public function setExpiration(?\DateTimeInterface $expiration): static
    {
        $this->expiration = $expiration;

        return $this;
    }

    // Vérifie si l'accès est expiré
    public function isExpired(): bool
    {
        return $this->expiration !== null && new \DateTime() > $this->expiration;
    }
}
