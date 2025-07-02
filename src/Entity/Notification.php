<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use ORM\HasLifecycleCallbacks;
use App\Entity\TypeNotification;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\Horodateur;
use App\Entity\StatutNotification;
use App\Entity\NiveauAccesNotification;
use App\Repository\NotificationRepository;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Notification
{

    use Horodateur;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string", enumType: TypeNotification::class, length: 255)]
    private TypeNotification $type;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(type: "string", enumType: StatutNotification::class, options: ['default' => 'unread'], length: 255)]
    private StatutNotification $statut;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    private ?DemandeAcces $demande_acces = null;

    #[ORM\Column(type: "string", enumType: NiveauAccesNotification::class, length: 255)]
    private NiveauAccesNotification $niveau_acces;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): TypeNotification
    {
        return $this->type;
    }

    public function setType(TypeNotification $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getStatut(): StatutNotification
    {
        return $this->statut;
    }

    public function setStatut(StatutNotification $statut): static
    {
        $this->statut = $statut;

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

    public function getDemandeAcces(): ?DemandeAcces
    {
        return $this->demande_acces;
    }

    public function setDemandeAcces(?DemandeAcces $demande_acces): static
    {
        $this->demande_acces = $demande_acces;

        return $this;
    }

    public function getNiveauAcces(): NiveauAccesNotification
    {
        return $this->niveau_acces;
    }

    public function setNiveauAcces(NiveauAccesNotification $niveau_acces): static
    {
        $this->niveau_acces = $niveau_acces;

        return $this;
    }
}
