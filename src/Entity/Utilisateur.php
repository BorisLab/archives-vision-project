<?php

namespace App\Entity;

use DateTimeInterface;
use App\Entity\Departement;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\StatutUtilisateur;
use App\Entity\Traits\Horodateur;
use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé')]
#[ORM\Index(columns: ['email'], name: 'idx_utilisateur_email')]
#[ORM\Index(columns: ['departement_id'], name: 'idx_utilisateur_departement')]
#[ORM\Index(columns: ['statut'], name: 'idx_utilisateur_statut')]
#[ORM\Index(columns: ['nom'], name: 'idx_utilisateur_nom')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{

    use Horodateur;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(type: "string", enumType: StatutUtilisateur::class, options: ['default' => 'inactive'], length: 255)]
    private StatutUtilisateur $statut;

    #[ORM\Column(length: 255)]
    private ?string $nom;

    #[ORM\Column(length: 255)]
    private ?string $prenoms;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $derniereActiv = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Departement $departement = null;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Dossier::class)]
    private Collection $dossiers;

    #[ORM\OneToMany(mappedBy: 'Utilisateur', targetEntity: DemandeAcces::class, orphanRemoval: true)]
    private Collection $demandeAcces;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Notification::class, orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoProfil = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isDG = null;

    public function __construct()
    {
        $this->dossiers = new ArrayCollection();
        $this->demandeAcces = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): StatutUtilisateur
    {
        return $this->statut;
    }

    public function setStatut(StatutUtilisateur $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenoms(): ?string
    {
        return $this->prenoms;
    }

    public function setPrenoms(string $prenoms): self
    {
        $this->prenoms = $prenoms;

        return $this;
    }

    public function getNomComplet(): ?string
    {
        return $this->prenoms . ' ' . $this->nom;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getDerniereActiv(): ?DateTimeInterface
    {
        return $this->derniereActiv;
    }

    public function setDerniereActiv(?\DateTimeInterface $derniereActiv): self
    {
        $this->derniereActiv = $derniereActiv;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    /**
     * @return Collection<int, Dossier>
     */
    public function getDossiers(): Collection
    {
        return $this->dossiers;
    }

    public function addDossier(Dossier $dossier): static
    {
        if (!$this->dossiers->contains($dossier)) {
            $this->dossiers->add($dossier);
            $dossier->setUtilisateur($this);
        }

        return $this;
    }

    public function removeDossier(Dossier $dossier): static
    {
        if ($this->dossiers->removeElement($dossier)) {
            // set the owning side to null (unless already changed)
            if ($dossier->getUtilisateur() === $this) {
                $dossier->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DemandeAcces>
     */
    public function getDemandeAcces(): Collection
    {
        return $this->demandeAcces;
    }

    public function addDemandeAcce(DemandeAcces $demandeAcce): static
    {
        if (!$this->demandeAcces->contains($demandeAcce)) {
            $this->demandeAcces->add($demandeAcce);
            $demandeAcce->setUtilisateur($this);
        }

        return $this;
    }

    public function removeDemandeAcce(DemandeAcces $demandeAcce): static
    {
        if ($this->demandeAcces->removeElement($demandeAcce)) {
            // set the owning side to null (unless already changed)
            if ($demandeAcce->getUtilisateur() === $this) {
                $demandeAcce->setUtilisateur(null);
            }
        }

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
            $notification->setUtilisateur($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUtilisateur() === $this) {
                $notification->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getPhotoProfil(): ?string
    {
        return $this->photoProfil;
    }

    public function setPhotoProfil(?string $photoProfil): static
    {
        $this->photoProfil = $photoProfil;

        return $this;
    }

    public function isDG(): ?bool
    {
        return $this->isDG;
    }

    public function setIsDG(?bool $isDG): static
    {
        $this->isDG = $isDG;

        return $this;
    }

}
