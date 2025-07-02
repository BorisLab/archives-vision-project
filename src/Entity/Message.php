<?php

namespace App\Entity;

use App\Entity\Utilisateur;
use App\Entity\StatutMessage;
use ORM\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\Horodateur;
use App\Repository\MessageRepository;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Message
{
    use Horodateur;
        
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'sentMessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $sender = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'receivedMessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $recipient = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column(type: "string", enumType: StatutMessage::class, options: ['default' => 'unread'], length: 255)]
    private StatutMessage $statut;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?Utilisateur
    {
        return $this->sender;
    }

    public function setSender(?Utilisateur $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getRecipient(): ?Utilisateur
    {
        return $this->recipient;
    }

    public function setRecipient(?Utilisateur $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getStatut(): StatutMessage
    {
        return $this->statut;
    }

    public function setStatut(StatutMessage $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
}
