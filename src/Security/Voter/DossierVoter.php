<?php

namespace App\Security\Voter;

use App\Entity\Dossier;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DossierVoter extends Voter
{
    const VIEW = 'DOSSIER_VIEW';
    const EDIT = 'DOSSIER_EDIT';
    const DELETE = 'DOSSIER_DELETE';
    const CREATE = 'DOSSIER_CREATE';
    const REQUEST_ACCESS = 'DOSSIER_REQUEST_ACCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Si c'est CREATE, on n'a pas besoin de subject (création d'un nouveau dossier)
        if ($attribute === self::CREATE) {
            return true;
        }

        // Pour les autres actions, on vérifie que le subject est un Dossier
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::REQUEST_ACCESS])
            && $subject instanceof Dossier;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // L'utilisateur doit être authentifié
        if (!$user instanceof Utilisateur) {
            return false;
        }

        // Pour CREATE, seul ARCHIVIST peut créer (ADMIN exclu)
        if ($attribute === self::CREATE) {
            return in_array('ROLE_ARCHIVIST', $user->getRoles());
        }

        /** @var Dossier $dossier */
        $dossier = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($dossier, $user),
            self::EDIT => $this->canEdit($dossier, $user),
            self::DELETE => $this->canDelete($dossier, $user),
            self::REQUEST_ACCESS => $this->canRequestAccess($dossier, $user),
            default => false,
        };
    }

    /**
     * Règles de visualisation :
     * - ADMIN : peut tout voir (lecture seule)
     * - ARCHIVIST : peut voir TOUS les dossiers (tous départements)
     * - USER : peut voir uniquement les dossiers dont il a reçu l'accès
     */
    private function canView(Dossier $dossier, Utilisateur $user): bool
    {
        // Admin peut voir (lecture seule)
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Archiviste peut voir TOUS les dossiers (peu importe le département)
        if (in_array('ROLE_ARCHIVIST', $user->getRoles())) {
            return true;
        }

        // User peut voir si accès accordé via DemandeAcces
        return $this->hasAccessGranted($dossier, $user);
    }

    /**
     * Règles d'édition :
     * - ADMIN : ne peut PAS modifier (seuls les archivistes gèrent les dossiers)
     * - ARCHIVIST : peut modifier TOUS les dossiers s'ils sont actifs
     * - USER : ne peut pas modifier
     */
    private function canEdit(Dossier $dossier, Utilisateur $user): bool
    {
        // Admin ne peut PAS modifier les dossiers
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return false;
        }

        // Archiviste peut modifier TOUS les dossiers actifs (peu importe le département)
        if (in_array('ROLE_ARCHIVIST', $user->getRoles())) {
            return $dossier->isStatut(); // statut = true signifie actif
        }

        // Users ne peuvent pas modifier
        return false;
    }

    /**
     * Règles de suppression :
     * - ADMIN : ne peut PAS supprimer (seuls les archivistes gèrent les dossiers)
     * - ARCHIVIST : peut supprimer TOUS les dossiers (la vérification du contenu se fait dans le contrôleur)
     * - USER : ne peut pas supprimer
     */
    private function canDelete(Dossier $dossier, Utilisateur $user): bool
    {
        // Admin ne peut PAS supprimer les dossiers
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return false;
        }

        // Archiviste peut supprimer TOUS les dossiers (peu importe le département)
        // La vérification du contenu (vide ou non) est faite dans le contrôleur
        if (in_array('ROLE_ARCHIVIST', $user->getRoles())) {
            return true;
        }

        // Users ne peuvent pas supprimer
        return false;
    }

    /**
     * Règles de demande d'accès :
     * - ADMIN : n'a pas besoin de demander accès
     * - ARCHIVIST : n'a pas besoin de demander accès (a déjà accès à son département)
     * - USER : peut demander accès si pas encore accordé
     */
    private function canRequestAccess(Dossier $dossier, Utilisateur $user): bool
    {
        // Admin et Archivist n'ont pas besoin de demander accès
        if (in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_ARCHIVIST', $user->getRoles())) {
            return false;
        }

        // User peut demander si pas déjà d'accès accordé
        return !$this->hasAccessGranted($dossier, $user);
    }

    /**
     * Vérifie si l'utilisateur a un accès accordé au dossier
     * (via DemandeAcces avec statut ACCORDEE)
     */
    private function hasAccessGranted(Dossier $dossier, Utilisateur $user): bool
    {
        foreach ($dossier->getDemandeAcces() as $demande) {
            if ($demande->getUtilisateur() === $user && $demande->getStatut() === 'ACCORDEE') {
                return true;
            }
        }

        return false;
    }
}
