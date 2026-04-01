<?php

namespace App\Entity;

use App\Entity\Traits\Horodateur;
use App\Repository\MouvementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MouvementRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(columns: ['type_mouvement'], name: 'idx_mouvement_type')]
#[ORM\Index(columns: ['date_mouvement'], name: 'idx_mouvement_date')]
#[ORM\Index(columns: ['utilisateur_id'], name: 'idx_mouvement_utilisateur')]
#[ORM\Index(columns: ['fichier_id'], name: 'idx_mouvement_fichier')]
#[ORM\Index(columns: ['dossier_id'], name: 'idx_mouvement_dossier')]
#[ORM\Index(columns: ['date_creation'], name: 'idx_mouvement_date_creation')]
class Mouvement
{
    use Horodateur;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le type de mouvement est obligatoire')]
    #[Assert\Choice(
        choices: ['arrivage', 'pret', 'reintegration', 'consultation', 'deplacement'],
        message: 'Type de mouvement invalide'
    )]
    private ?string $type_mouvement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'La date du mouvement est obligatoire')]
    private ?\DateTimeInterface $date_mouvement = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Fichier::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Fichier $fichier = null;

    #[ORM\ManyToOne(targetEntity: Dossier::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Dossier $dossier = null;

    #[ORM\ManyToOne(targetEntity: BoitePhysique::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?BoitePhysique $boite_destination = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emprunteur_nom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_retour_prevue = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_retour_effective = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(
        choices: ['en_cours', 'termine', 'en_retard'],
        message: 'Statut invalide'
    )]
    private ?string $statut = 'en_cours';

    public function __construct()
    {
        $this->date_mouvement = new \DateTime();
        $this->statut = 'en_cours';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeMouvement(): ?string
    {
        return $this->type_mouvement;
    }

    public function setTypeMouvement(string $type_mouvement): static
    {
        $this->type_mouvement = $type_mouvement;
        return $this;
    }

    public function getDateMouvement(): ?\DateTimeInterface
    {
        return $this->date_mouvement;
    }

    public function setDateMouvement(\DateTimeInterface $date_mouvement): static
    {
        $this->date_mouvement = $date_mouvement;
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

    public function getFichier(): ?Fichier
    {
        return $this->fichier;
    }

    public function setFichier(?Fichier $fichier): static
    {
        $this->fichier = $fichier;
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

    public function getBoiteDestination(): ?BoitePhysique
    {
        return $this->boite_destination;
    }

    public function setBoiteDestination(?BoitePhysique $boite_destination): static
    {
        $this->boite_destination = $boite_destination;
        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;
        return $this;
    }

    public function getEmprunteurNom(): ?string
    {
        return $this->emprunteur_nom;
    }

    public function setEmprunteurNom(?string $emprunteur_nom): static
    {
        $this->emprunteur_nom = $emprunteur_nom;
        return $this;
    }

    public function getDateRetourPrevue(): ?\DateTimeInterface
    {
        return $this->date_retour_prevue;
    }

    public function setDateRetourPrevue(?\DateTimeInterface $date_retour_prevue): static
    {
        $this->date_retour_prevue = $date_retour_prevue;
        return $this;
    }

    public function getDateRetourEffective(): ?\DateTimeInterface
    {
        return $this->date_retour_effective;
    }

    public function setDateRetourEffective(?\DateTimeInterface $date_retour_effective): static
    {
        $this->date_retour_effective = $date_retour_effective;
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

    /**
     * Vérifie si le mouvement est en retard (pour les prêts)
     */
    public function isEnRetard(): bool
    {
        if ($this->type_mouvement !== 'pret' || $this->statut === 'termine') {
            return false;
        }

        if (!$this->date_retour_prevue) {
            return false;
        }

        return new \DateTime() > $this->date_retour_prevue;
    }

    /**
     * Calcule le nombre de jours de retard
     */
    public function getJoursRetard(): int
    {
        if (!$this->isEnRetard()) {
            return 0;
        }

        $now = new \DateTime();
        $diff = $now->diff($this->date_retour_prevue);
        return $diff->days;
    }

    /**
     * Retourne un libellé lisible du type de mouvement
     */
    public function getTypeMouvementLibelle(): string
    {
        return match($this->type_mouvement) {
            'arrivage' => 'Arrivage',
            'pret' => 'Prêt',
            'reintegration' => 'Réintégration',
            'consultation' => 'Consultation sur place',
            'deplacement' => 'Déplacement',
            default => $this->type_mouvement
        };
    }

    public function __toString(): string
    {
        $entity = $this->fichier ? 'Fichier #'.$this->fichier->getFichierId() : 'Dossier #'.$this->dossier?->getDossierId();
        return $this->getTypeMouvementLibelle() . ' - ' . $entity . ' (' . $this->date_mouvement->format('d/m/Y') . ')';
    }
}
