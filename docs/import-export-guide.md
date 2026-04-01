# Import/Export CSV/XLSX - Documentation

## Vue d'ensemble

Le module d'import/export permet de gérer en masse les dossiers et fichiers d'archives via des fichiers Excel (.xlsx) ou CSV (.csv).

## Fonctionnalités

### 📤 Export

#### Export de Dossiers

- **Formats disponibles** : Excel (.xlsx) et CSV (.csv)
- **Données exportées** :
  - ID, Libellé, Format, Tags
  - Département, Créateur, Dossier Parent
  - Statut, Dates (début/fin)
  - Typologie documentaire, Règle de rétention
  - Date de création

#### Export de Fichiers

- **Formats disponibles** : Excel (.xlsx) et CSV (.csv)
- **Données exportées** :
  - ID, Libellé, Type, Format, Tags
  - Dossier, Département, Boîte Physique
  - Statut, Dates (début/fin)
  - Typologie documentaire, Règle de rétention
  - Chemin d'accès, Date de création

**Routes d'export** :

- `/archivist/import-export/export/dossiers/xlsx`
- `/archivist/import-export/export/dossiers/csv`
- `/archivist/import-export/export/fichiers/xlsx`
- `/archivist/import-export/export/fichiers/csv`

### 📥 Import

#### Templates d'import

Des templates Excel pré-formatés sont disponibles au téléchargement :

- `template_import_dossiers.xlsx` : Structure pour importer des dossiers
- `template_import_fichiers.xlsx` : Structure pour importer des fichiers

Les templates incluent :

- **Ligne 1** : Noms des champs techniques
- **Ligne 2** : Descriptions et instructions
- **Ligne 3** : Exemple de données

**Routes des templates** :

- `/archivist/import-export/template/dossiers`
- `/archivist/import-export/template/fichiers`

#### Import de Dossiers

**Champs obligatoires** :

- `libelle_dossier` : Nom du dossier
- `departement_id` : ID du département existant

**Champs optionnels** :

- `format` : "Numérique" ou "Physique" (défaut: Numérique)
- `tags` : Tags séparés par virgules
- `dossier_parent_id` : ID du dossier parent (pour créer une hiérarchie)
- `statut` : 1 (actif) ou 0 (inactif) - défaut: 1
- `date_debut` : Date de début (format YYYY-MM-DD)
- `date_fin` : Date de fin (format YYYY-MM-DD)
- `typologie_documentaire` : Type de documents contenus
- `regle_retention_id` : ID de la règle de rétention

**Exemple de ligne d'import** :

```
Mon Dossier 2024 | Numérique | admin,important | 1 | | 1 | 2024-01-01 | 2024-12-31 | Administratif | 2
```

#### Import de Fichiers

**Champs obligatoires** :

- `libelle_fichier` : Nom du fichier
- `type` : Type de fichier (Document, Image, etc.)
- `dossier_id` : ID du dossier parent existant

**Champs optionnels** :

- `format` : "Numérique" ou "Physique" (défaut: Numérique)
- `tags` : Tags séparés par virgules
- `boite_physique_id` : ID de la boîte physique (pour archives physiques)
- `statut` : 1 (disponible) ou 0 (indisponible) - défaut: 1
- `date_debut` : Date de début (format YYYY-MM-DD)
- `date_fin` : Date de fin (format YYYY-MM-DD)
- `typologie_documentaire` : Type de document
- `regle_retention_id` : ID de la règle de rétention
- `chemin_acces` : Chemin du fichier numérique

**Exemple de ligne d'import** :

```
Contrat 2024.pdf | Document | Numérique | contrat,juridique | 5 | | 1 | 2024-01-01 | 2029-01-01 | Contrat | 3 | /uploads/contrats/contrat2024.pdf
```

**Routes d'import** :

- `/archivist/import-export/import/dossiers` (POST)
- `/archivist/import-export/import/fichiers` (POST)

## Architecture technique

### Services

#### ExportService (`src/Service/ExportService.php`)

**Méthodes principales** :

```php
public function exportDossiers(array $dossiers, string $format = 'xlsx'): string
```

- Génère un fichier Excel ou CSV contenant les dossiers
- Applique un style aux en-têtes (fond bleu, texte blanc)
- Auto-dimensionne les colonnes
- Retourne le chemin du fichier généré

```php
public function exportFichiers(array $fichiers, string $format = 'xlsx'): string
```

- Génère un fichier Excel ou CSV contenant les fichiers
- Applique un style aux en-têtes (fond vert, texte blanc)
- Retourne le chemin du fichier généré

```php
public function generateDossiersTemplate(): string
public function generateFichiersTemplate(): string
```

- Génèrent des templates Excel pré-formatés avec :
  - Noms de champs (ligne 1)
  - Descriptions (ligne 2)
  - Exemple de données (ligne 3)

#### ImportService (`src/Service/ImportService.php`)

**Méthodes principales** :

```php
public function importDossiers(string $filepath): array
```

- Lit un fichier Excel/CSV
- Valide chaque ligne
- Crée les entités Dossier
- Retourne un tableau avec le nombre de succès, erreurs et warnings

```php
public function importFichiers(string $filepath): array
```

- Lit un fichier Excel/CSV
- Valide chaque ligne
- Crée les entités Fichier
- Retourne un tableau avec le nombre de succès, erreurs et warnings

**Validation** :

- Champs obligatoires vérifiés
- Vérification de l'existence des entités référencées (département, dossier parent, etc.)
- Format des dates validé
- Messages d'erreur détaillés avec numéro de ligne

**Gestion des erreurs** :

- **Erreurs** : Empêchent l'import de la ligne (ex: champ obligatoire manquant)
- **Warnings** : N'empêchent pas l'import mais signalent un problème (ex: ID de référence introuvable)

### Controller

#### ImportExportController (`src/Controller/ImportExportController.php`)

**Routes** :

- `GET /archivist/import-export/` : Page principale
- `GET /export/dossiers/{format}` : Export des dossiers
- `GET /export/fichiers/{format}` : Export des fichiers
- `GET /template/dossiers` : Télécharger template dossiers
- `GET /template/fichiers` : Télécharger template fichiers
- `POST /import/dossiers` : Importer des dossiers
- `POST /import/fichiers` : Importer des fichiers

**Sécurité** :

- Accès réservé aux utilisateurs avec `ROLE_ARCHIVIST`
- Validation CSRF sur les formulaires d'upload
- Vérification des extensions de fichiers acceptées
- Fichiers temporaires supprimés après traitement

**Audit** :

- Tous les imports/exports sont enregistrés dans les logs d'audit
- Informations tracées :
  - Format utilisé
  - Nombre d'éléments traités
  - Filtres appliqués (pour exports)
  - Compteurs de succès/erreurs/warnings (pour imports)

## Utilisation

### Export

1. Se rendre sur `/archivist/import-export/`
2. Section **Export**, choisir "Dossiers" ou "Fichiers"
3. Cliquer sur "Excel (.xlsx)" ou "CSV"
4. Le fichier est téléchargé automatiquement

### Import

1. Se rendre sur `/archivist/import-export/`
2. Section **Import**, choisir "Dossiers" ou "Fichiers"
3. Télécharger le template correspondant
4. Remplir le template avec vos données (à partir de la ligne 4)
5. Uploader le fichier complété
6. Cliquer sur "Importer"
7. Consulter les messages de succès/erreurs/warnings

## Formats de fichiers

### Excel (.xlsx)

- Format recommandé pour imports complexes
- Supporte les styles et formatage
- Meilleure lisibilité dans Excel/LibreOffice

### CSV (.csv)

- Format léger et universel
- Séparateur : `;` (point-virgule)
- Encodage : UTF-8
- Enclosure : `"` (guillemets doubles)

## Exemples d'utilisation

### Export de tous les dossiers d'un département

```php
// Dans un controller personnalisé
$dossiers = $dossierRepo->findBy(['departement' => $departementId]);
$filepath = $exportService->exportDossiers($dossiers, 'xlsx');
```

### Import depuis un script

```php
$result = $importService->importDossiers('/path/to/import.xlsx');

echo "Succès : {$result['success']}\n";
foreach ($result['errors'] as $error) {
    echo "Erreur : $error\n";
}
```

## Limitations et bonnes pratiques

### Limitations

- **Taille de fichier** : Limitée par `upload_max_filesize` de PHP (configurable dans php.ini)
- **Mémoire** : Import de gros fichiers peut nécessiter plus de mémoire
- **Timeout** : Les gros imports peuvent prendre du temps

### Bonnes pratiques

- ✅ **Valider les données** avant l'import (vérifier les IDs de référence)
- ✅ **Importer par lots** : Séparer les gros imports en plusieurs fichiers
- ✅ **Tester avec un petit échantillon** avant l'import complet
- ✅ **Sauvegarder avant import** : Faire un backup de la base de données
- ✅ **Vérifier les logs** : Consulter les messages d'erreurs et warnings
- ✅ **Utiliser les templates** : Toujours partir du template officiel

### Conseils de performance

- Pour les très gros volumes (>10 000 lignes), préférer un import en ligne de commande
- Utiliser le format CSV pour les imports massifs (plus rapide)
- Désactiver temporairement les index lors d'imports massifs

## Dépannage

### "Format de fichier invalide"

➡️ Vérifier que le fichier est bien au format .xlsx, .xls ou .csv

### "Département avec l'ID X introuvable"

➡️ Vérifier que l'ID du département existe dans la base de données
➡️ Exporter la liste des départements pour obtenir les IDs valides

### "Le libellé du dossier est obligatoire"

➡️ La colonne `libelle_dossier` est vide sur cette ligne
➡️ Remplir le champ ou supprimer la ligne

### Import ne prend pas en compte les lignes

➡️ Vérifier que les données commencent bien à la ligne 4 (après en-têtes + description + exemple)
➡️ S'assurer qu'il n'y a pas de lignes totalement vides

### "The zip extension is missing"

➡️ Activer l'extension PHP `zip` dans php.ini
➡️ Ou utiliser l'option `--ignore-platform-req=ext-zip` avec Composer

## Évolutions futures possibles

- 🔄 Import incrémental (mise à jour des entités existantes)
- 📊 Preview avant import (affichage des données avant validation)
- 🔍 Validation avancée avec règles métier personnalisées
- 📧 Notification par email après import massif
- 📈 Statistiques d'import (temps d'exécution, taux de succès)
- 🗄️ Historique des imports avec possibilité de rollback
