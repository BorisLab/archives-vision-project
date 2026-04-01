<?php

namespace App\Security\Voter;

use App\Entity\DemandeAcces;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DemandeAccesVoter extends Voter
{
    const CREATE = 'DEMANDE_ACCES_CREATE';
    const APPROVE = 'DEMANDE_ACCES_APPROVE';
    const REJECT = 'DEMANDE_ACCES_REJECT';
    const VIEW = 'DEMANDE_ACCES_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // CREATE n'a pas besoin de subject
        if ($attribute === self::CREATE) {
            return true;
        }

        return in_array($attribute, [self::APPROVE, self::REJECT, self::VIEW])
            && $subject instanceof DemandeAcces;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Utilisateur) {
            return false;
        }

        // ADMIN peut tout faire
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($user);
            case self::APPROVE:
            case self::REJECT:
                return $this->canApproveOrReject($subject, $user);
            case self::VIEW:
                return $this->canView($subject, $user);
        }

        return false;
    }

    private function canCreate(Utilisateur $user): bool
    {
        // Seuls les USER peuvent créer des demandes d'accès
        return in_array('ROLE_USER', $user->getRoles()) && !in_array('ROLE_ARCHIVIST', $user->getRoles());
    }

    private function canApproveOrReject(?DemandeAcces $demandeAcces, Utilisateur $user): bool
    {
        // Seuls ARCHIVIST peut approuver/rejeter
        if (!in_array('ROLE_ARCHIVIST', $user->getRoles())) {
            return false;
        }

        // L'archiviste doit être celui qui gère le département du dossier/fichier concerné
        // Pour simplifier, on autorise tous les archivistes pour l'instant
        return true;
    }

    private function canView(?DemandeAcces $demandeAcces, Utilisateur $user): bool
    {
        if (!$demandeAcces) {
            return false;
        }

        // L'utilisateur peut voir sa propre demande
        if ($demandeAcces->getUtilisateur()->getId() === $user->getId()) {
            return true;
        }

        // Les ARCHIVIST peuvent voir toutes les demandes
        return in_array('ROLE_ARCHIVIST', $user->getRoles());
    }
}
