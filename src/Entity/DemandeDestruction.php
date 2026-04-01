<?php

namespace App\Entity;

use App\Repository\DemandeDestructionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeDestructionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class DemandeDestruction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $type_entite = null; // 'Dossier' ou 'Fichier'

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $entite_id = null; // ID du dossier ou fichier

    #[ORM\Column(type: Types::TEXT)]
    private ?string $libelle_entite = null; // Nom du dossier/fichier (pour historique)

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $demandeur = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Utilisateur $approbateur = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = null; // EN_ATTENTE, APPROUVEE, REJETEE

    #[ORM\Column(type: Types::TEXT)]
    private ?string $justification = null; // Raison de la demande de destruction

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motif_rejet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichier_preuve = null; // Chemin du PDF d'autorisation

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_demande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_traitement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_execution = null;

    #[ORM\PrePersist]
    public function setDateDemandeValue(): void
    {
        $this->date_demande = new \DateTime();
        $this->statut = 'EN_ATTENTE';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeEntite(): ?string
    {
        return $this->type_entite;
    }

    public function setTypeEntite(string $type_entite): static
    {
        $this->type_entite = $type_entite;
        return $this;
    }

    public function getEntiteId(): ?int
    {
        return $this->entite_id;
    }

    public function setEntiteId(int $entite_id): static
    {
        $this->entite_id = $entite_id;
        return $this;
    }

    public function getLibelleEntite(): ?string
    {
        return $this->libelle_entite;
    }

    public function setLibelleEntite(string $libelle_entite): static
    {
        $this->libelle_entite = $libelle_entite;
        return $this;
    }

    public function getDemandeur(): ?Utilisateur
    {
        return $this->demandeur;
    }

    public function setDemandeur(?Utilisateur $demandeur): static
    {
        $this->demandeur = $demandeur;
        return $this;
    }

    public function getApprobateur(): ?Utilisateur
    {
        return $this->approbateur;
    }

    public function setApprobateur(?Utilisateur $approbateur): static
    {
        $this->approbateur = $approbateur;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getJustification(): ?string
    {
        return $this->justification;
    }

    public function setJustification(string $justification): static
    {
        $this->justification = $justification;
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

    public function getFichierPreuve(): ?string
    {
        return $this->fichier_preuve;
    }

    public function setFichierPreuve(?string $fichier_preuve): static
    {
        $this->fichier_preuve = $fichier_preuve;
        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->date_demande;
    }

    public function setDateDemande(\DateTimeInterface $date_demande): static
    {
        $this->date_demande = $date_demande;
        return $this;
    }

    public function getDateTraitement(): ?\DateTimeInterface
    {
        return $this->date_traitement;
    }

    public function setDateTraitement(?\DateTimeInterface $date_traitement): static
    {
        $this->date_traitement = $date_traitement;
        return $this;
    }

    public function getDateExecution(): ?\DateTimeInterface
    {
        return $this->date_execution;
    }

    public function setDateExecution(?\DateTimeInterface $date_execution): static
    {
        $this->date_execution = $date_execution;
        return $this;
    }
}
