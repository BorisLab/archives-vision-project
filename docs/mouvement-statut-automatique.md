# Mise à jour automatique du statut des Dossiers/Fichiers

## Vue d'ensemble

Le système de mouvements physiques met automatiquement à jour le statut des dossiers et fichiers en fonction du type de mouvement enregistré.

## Règles métier

| Type de mouvement | Impact sur le statut            | Description                                                       |
| ----------------- | ------------------------------- | ----------------------------------------------------------------- |
| **Arrivage**      | `statut = true` (disponible)    | Nouvelles archives qui arrivent dans le dépôt                     |
| **Prêt**          | `statut = false` (indisponible) | Archives sorties et prêtées à un emprunteur externe               |
| **Réintégration** | `statut = true` (disponible)    | Retour des archives après un prêt → redeviennent disponibles      |
| **Consultation**  | `statut = false` (indisponible) | Archives en consultation sur place → temporairement indisponibles |
| **Déplacement**   | Statut inchangé                 | Simple changement de localisation physique (changement de boîte)  |

## Implémentation technique

### 1. Méthode dans MouvementController

La méthode `updateEntityStatus()` est appelée automatiquement lors :

- De la création d'un mouvement (`new()`)
- De la modification d'un mouvement (`edit()`)
- D'une réintégration (`reintegrer()`)

```php
private function updateEntityStatus(Mouvement $mouvement): void
{
    // Déterminer le nouveau statut selon le type de mouvement
    $nouveauStatut = match($mouvement->getTypeMouvement()) {
        'arrivage', 'reintegration' => true,  // Disponible
        'pret', 'consultation' => false,       // Indisponible
        'deplacement' => null,                 // Pas de changement
        default => null
    };

    // Si pas de changement de statut requis, on sort
    if ($nouveauStatut === null) {
        return;
    }

    // Appliquer le nouveau statut sur le fichier ou le dossier concerné
    if ($mouvement->getFichier()) {
        $mouvement->getFichier()->setStatut($nouveauStatut);
    }

    if ($mouvement->getDossier()) {
        $mouvement->getDossier()->setStatut($nouveauStatut);
    }
}
```

### 2. Intégration dans les actions du controller

#### Création d'un mouvement

```php
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    // ... validation du formulaire ...

    $entityManager->persist($mouvement);

    // ⚡ Mise à jour automatique du statut
    $this->updateEntityStatus($mouvement);

    $entityManager->flush();

    // ... audit log ...
}
```

#### Réintégration après prêt

```php
public function reintegrer(Request $request, Mouvement $mouvement, EntityManagerInterface $entityManager): Response
{
    // Créer un mouvement de réintégration
    $reintegration = new Mouvement();
    $reintegration->setTypeMouvement('reintegration');
    // ... configuration du mouvement ...

    $entityManager->persist($reintegration);

    // ⚡ Mise à jour automatique du statut → redevient disponible
    $this->updateEntityStatus($reintegration);

    $entityManager->flush();
}
```

## Exemples de scénarios

### Scénario 1 : Prêt d'archives

1. **État initial** : Fichier #42 → statut = `true` (disponible)
2. **Action** : Création mouvement type "prêt" pour Fichier #42
3. **Résultat automatique** : Fichier #42 → statut = `false` (indisponible)

### Scénario 2 : Retour après prêt

1. **État initial** : Fichier #42 → statut = `false` (en prêt)
2. **Action** : Réintégration du Fichier #42
3. **Résultat automatique** :
   - Mouvement de prêt → statut = "terminé"
   - Fichier #42 → statut = `true` (disponible à nouveau)

### Scénario 3 : Déplacement physique

1. **État initial** : Fichier #42 → statut = `true`, boîte BP-2024-001
2. **Action** : Déplacement vers boîte BP-2024-005
3. **Résultat automatique** : Fichier #42 → statut = `true` (inchangé), nouvelle boîte

## Avantages

✅ **Automatisation** : Plus besoin de mettre à jour manuellement le statut  
✅ **Cohérence** : Le statut reflète toujours l'état réel des archives  
✅ **Traçabilité** : Tous les changements de statut sont liés à un mouvement dans l'historique  
✅ **Simplicité** : Logique centralisée dans une seule méthode privée  
✅ **Audit** : Combiné avec le système d'audit logs pour une traçabilité complète

## Tests

Un script de test complet est disponible : `tests/test_mouvement_statut.php`

Ce script teste les 3 scénarios principaux :

1. ✅ Prêt → fichier devient indisponible
2. ✅ Réintégration → fichier redevient disponible
3. ✅ Déplacement → statut reste inchangé

## Impact sur l'interface utilisateur

Dans les listes de fichiers/dossiers, le statut s'affiche visuellement :

- 🟢 **Badge vert "Disponible"** : statut = `true`
- 🔴 **Badge rouge "Indisponible"** : statut = `false`

Le système empêche également les actions incohérentes, par exemple :

- ❌ Impossible de créer un prêt pour un fichier déjà en prêt
- ❌ Impossible de réintégrer un fichier qui n'est pas en prêt

## Évolutions futures possibles

- 📊 Statistiques sur le taux de disponibilité par période
- 🔔 Alertes automatiques pour les prêts dépassant la durée prévue
- 📧 Notifications aux emprunteurs avant la date de retour
- 🔄 Gestion des réservations (file d'attente quand archive indisponible)
