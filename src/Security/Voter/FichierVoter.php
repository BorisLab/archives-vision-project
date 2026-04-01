<?php

namespace App\Security\Voter;

use App\Entity\Fichier;
use App\Entity\Utilisateur;
use App\Entity\DemandeAcces;
use App\Entity\StatutDemandeAcces;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FichierVoter extends Voter
{
    const VIEW = 'FICHIER_VIEW';
    const DOWNLOAD = 'FICHIER_DOWNLOAD';
    const EDIT = 'FICHIER_EDIT';
    const DELETE = 'FICHIER_DELETE';
    const CREATE = 'FICHIER_CREATE';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Si c'est CREATE, on n'a pas besoin de subject
        if ($attribute === self::CREATE) {
            return true;
        }

        return in_array($attribute, [self::VIEW, self::DOWNLOAD, self::EDIT, self::DELETE])
            && $subject instanceof Fichier;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Utilisateur) {
            return false;
        }

        // Pour CREATE, seul ARCHIVIST (ADMIN exclu)
        if ($attribute === self::CREATE) {
            return in_array('ROLE_ARCHIVIST', $user->getRoles());
        }

        /** @var Fichier $fichier */
        $fichier = $subject;

        return match ($attribute) {
            self::VIEW, self::DOWNLOAD => $this->canViewOrDownload($fichier, $user),
            self::EDIT => $this->canEdit($fichier, $user),
            self::DELETE => $this->canDelete($fichier, $user),
            default => false,
        };
    }

    /**
     * Règles de visualisation/téléchargement :
     * - ADMIN : peut tout voir (lecture seule)
     * - ARCHIVIST : peut voir TOUS les fichiers (tous départements)
     * - USER : peut voir si accès accordé via DemandeAcces
     */
    private function canViewOrDownload(Fichier $fichier, Utilisateur $user): bool
    {
        // Admin peut voir (lecture seule)
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Archiviste peut voir TOUS les fichiers (peu importe le département)
        if (in_array('ROLE_ARCHIVIST', $user->getRoles())) {
            return true;
        }

        // USER peut voir/télécharger si accès accordé via DemandeAcces
        $demandeAcces = $this->entityManager->getRepository(DemandeAcces::class)->findOneBy([
            'fichier' => $fichier->getFichierId(),
            'utilisateur' => $user->getId(),
            'statut' => 'ACCORDEE' // Utiliser string au lieu de StatutDemandeAcces::APPROUVEE
        ]);

        return $demandeAcces !== null;
    }

    /**
     * Règles d'édition :
     * - ADMIN : ne peut PAS modifier (seuls les archivistes gèrent les fichiers)
     * - ARCHIVIST : peut modifier TOUS les fichiers actifs
     * - USER : ne peut pas modifier
     */
    private function canEdit(Fichier $fichier, Utilisateur $user): bool
    {
        // Admin ne peut PAS modifier les fichiers
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return false;
        }

        // Archiviste peut modifier TOUS les fichiers actifs (peu importe le département)
        if (in_array('ROLE_ARCHIVIST', $user->getRoles())) {
            return $fichier->isStatut(); // statut = true signifie actif
        }

        // Users ne peuvent pas modifier
        return false;
    }

    /**
     * Règles de suppression :
     * - ADMIN : ne peut PAS supprimer (seuls les archivistes gèrent les fichiers)
     * - ARCHIVIST : peut supprimer TOUS les fichiers
     * - USER : ne peut pas supprimer
     */
    private function canDelete(Fichier $fichier, Utilisateur $user): bool
    {
        // Admin ne peut PAS supprimer les fichiers
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return false;
        }

        // Archiviste peut supprimer TOUS les fichiers (peu importe le département)
        if (in_array('ROLE_ARCHIVIST', $user->getRoles())) {
            return true;
        }

        // Users ne peuvent pas supprimer
        return false;
    }
}
