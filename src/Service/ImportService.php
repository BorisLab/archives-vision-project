<?php

namespace App\Service;

use App\Entity\Dossier;
use App\Entity\Fichier;
use App\Repository\DepartementRepository;
use App\Repository\DossierRepository;
use App\Repository\RegleRetentionRepository;
use App\Repository\BoitePhysiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportService
{
    private EntityManagerInterface $entityManager;
    private DepartementRepository $departementRepo;
    private DossierRepository $dossierRepo;
    private RegleRetentionRepository $regleRetentionRepo;
    private BoitePhysiqueRepository $boitePhysiqueRepo;
    private FileIntegrityService $fileIntegrityService;
    private array $errors = [];
    private array $warnings = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        DepartementRepository $departementRepo,
        DossierRepository $dossierRepo,
        RegleRetentionRepository $regleRetentionRepo,
        BoitePhysiqueRepository $boitePhysiqueRepo,
        FileIntegrityService $fileIntegrityService
    ) {
        $this->entityManager = $entityManager;
        $this->departementRepo = $departementRepo;
        $this->dossierRepo = $dossierRepo;
        $this->regleRetentionRepo = $regleRetentionRepo;
        $this->boitePhysiqueRepo = $boitePhysiqueRepo;
        $this->fileIntegrityService = $fileIntegrityService;
    }

    /**
     * Importe des dossiers depuis un fichier Excel ou CSV
     * 
     * @param string $filepath Chemin du fichier à importer
     * @param mixed $user Utilisateur courant
     * @return array ['success' => int, 'errors' => array, 'warnings' => array]
     */
    public function importDossiers(string $filepath, $user): array
    {
        $this->errors = [];
        $this->warnings = [];
        $successCount = 0;

        try {
            $spreadsheet = IOFactory::load($filepath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Vérifier que le fichier n'est pas vide
            if (count($rows) < 2) {
                $this->errors[] = "Le fichier est vide ou ne contient pas de données";
                return $this->buildResult($successCount);
            }

            // La première ligne contient les en-têtes
            $headers = array_shift($rows);
            
            // Si la ligne 2 contient des descriptions, on la saute
            if (isset($rows[0][0]) && strpos($rows[0][0], 'obligatoire') !== false) {
                array_shift($rows);
            }

            // Ligne 3 est l'exemple, on la saute aussi
            if (isset($rows[0][0]) && (strpos($rows[0][0], 'Test') !== false || strpos($rows[0][0], 'Exemple') !== false)) {
                array_shift($rows);
            }

            $lineNumber = 4; // Commence à 4 (après en-têtes + description + exemple)

            foreach ($rows as $row) {
                // Ignorer les lignes vides
                if (empty(array_filter($row))) {
                    $lineNumber++;
                    continue;
                }

                try {
                    $dossier = $this->createDossierFromRow($row, $headers, $lineNumber, $user);
                    if ($dossier) {
                        $this->entityManager->persist($dossier);
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $this->errors[] = "Ligne $lineNumber : " . $e->getMessage();
                }

                $lineNumber++;
            }

            // Sauvegarder tous les dossiers en une fois
            if ($successCount > 0) {
                $this->entityManager->flush();
            }

        } catch (\Exception $e) {
            $this->errors[] = "Erreur lors de la lecture du fichier : " . $e->getMessage();
        }

        return $this->buildResult($successCount);
    }

    /**
     * Importe des fichiers depuis un fichier Excel ou CSV
     * 
     * @param string $filepath Chemin du fichier à importer
     * @param mixed $user Utilisateur courant
     * @return array ['success' => int, 'errors' => array, 'warnings' => array]
     */
    public function importFichiers(string $filepath, $user): array
    {
        $this->errors = [];
        $this->warnings = [];
        $successCount = 0;

        try {
            $spreadsheet = IOFactory::load($filepath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) < 2) {
                $this->errors[] = "Le fichier est vide ou ne contient pas de données";
                return $this->buildResult($successCount);
            }

            $headers = array_shift($rows);
            
            // Sauter les lignes de description et d'exemple
            if (isset($rows[0][0]) && strpos($rows[0][0], 'obligatoire') !== false) {
                array_shift($rows);
            }
            if (isset($rows[0][0]) && (strpos($rows[0][0], 'Test') !== false || strpos($rows[0][0], 'Exemple') !== false)) {
                array_shift($rows);
            }

            $lineNumber = 4;

            foreach ($rows as $row) {
                if (empty(array_filter($row))) {
                    $lineNumber++;
                    continue;
                }

                try {
                    $fichier = $this->createFichierFromRow($row, $headers, $lineNumber);
                    if ($fichier) {
                        $this->entityManager->persist($fichier);
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $this->errors[] = "Ligne $lineNumber : " . $e->getMessage();
                }

                $lineNumber++;
            }

            if ($successCount > 0) {
                $this->entityManager->flush();
            }

        } catch (\Exception $e) {
            $this->errors[] = "Erreur lors de la lecture du fichier : " . $e->getMessage();
        }

        return $this->buildResult($successCount);
    }

    /**
     * Crée un dossier à partir d'une ligne du fichier
     */
    private function createDossierFromRow(array $row, array $headers, int $lineNumber, $user): ?Dossier
    {
        $data = array_combine($headers, $row);

        // Validation des champs obligatoires
        if (empty($data['libelle_dossier'])) {
            throw new \Exception("Le libellé du dossier est obligatoire");
        }

        if (empty($data['departement_id'])) {
            throw new \Exception("L'ID du département est obligatoire");
        }

        // Récupérer le département
        $departement = $this->departementRepo->find((int)$data['departement_id']);
        if (!$departement) {
            throw new \Exception("Département avec l'ID {$data['departement_id']} introuvable");
        }

        // Créer le dossier
        $dossier = new Dossier();
        $dossier->setLibelleDossier($data['libelle_dossier']);
        $dossier->setFormat($data['format'] ?? 'Numérique');
        $dossier->setTags($data['tags'] ?? null);
        $dossier->setDepartement($departement);
        $dossier->setUtilisateur($user);
        $dossier->setStatut(!empty($data['statut']) ? (bool)$data['statut'] : true);

        // Dossier parent (optionnel)
        if (!empty($data['dossier_parent_id'])) {
            $parent = $this->dossierRepo->find((int)$data['dossier_parent_id']);
            if ($parent) {
                $dossier->setDossierParent($parent);
            } else {
                $this->warnings[] = "Ligne $lineNumber : Dossier parent {$data['dossier_parent_id']} introuvable";
            }
        }

        // Dates (optionnelles)
        if (!empty($data['date_debut'])) {
            try {
                $dossier->setDateDebut(new \DateTime($data['date_debut']));
            } catch (\Exception $e) {
                $this->warnings[] = "Ligne $lineNumber : Format de date_debut invalide";
            }
        }

        if (!empty($data['date_fin'])) {
            try {
                $dossier->setDateFin(new \DateTime($data['date_fin']));
            } catch (\Exception $e) {
                $this->warnings[] = "Ligne $lineNumber : Format de date_fin invalide";
            }
        }

        // Typologie (optionnelle)
        if (!empty($data['typologie_documentaire'])) {
            $dossier->setTypologieDocumentaire($data['typologie_documentaire']);
        }

        // Règle de rétention (optionnelle)
        if (!empty($data['regle_retention_id'])) {
            $regle = $this->regleRetentionRepo->find((int)$data['regle_retention_id']);
            if ($regle) {
                $dossier->setRegleRetention($regle);
            } else {
                $this->warnings[] = "Ligne $lineNumber : Règle de rétention {$data['regle_retention_id']} introuvable";
            }
        }

        return $dossier;
    }

    /**
     * Crée un fichier à partir d'une ligne du fichier
     */
    private function createFichierFromRow(array $row, array $headers, int $lineNumber): ?Fichier
    {
        $data = array_combine($headers, $row);

        // Validation des champs obligatoires
        if (empty($data['libelle_fichier'])) {
            throw new \Exception("Le libellé du fichier est obligatoire");
        }

        if (empty($data['type'])) {
            throw new \Exception("Le type de fichier est obligatoire");
        }

        if (empty($data['dossier_id'])) {
            throw new \Exception("L'ID du dossier est obligatoire");
        }

        // Récupérer le dossier
        $dossier = $this->dossierRepo->find((int)$data['dossier_id']);
        if (!$dossier) {
            throw new \Exception("Dossier avec l'ID {$data['dossier_id']} introuvable");
        }

        // Créer le fichier
        $fichier = new Fichier();
        $fichier->setLibelleFichier($data['libelle_fichier']);
        $fichier->setType($data['type']);
        $fichier->setFormat($data['format'] ?? 'Numérique');
        $fichier->setTags($data['tags'] ?? null);
        $fichier->setDossier($dossier);
        $fichier->setStatut(!empty($data['statut']) ? (bool)$data['statut'] : true);
        $fichier->setCheminAcces($data['chemin_acces'] ?? '');

        // Boîte physique (optionnelle)
        if (!empty($data['boite_physique_id'])) {
            $boite = $this->boitePhysiqueRepo->find((int)$data['boite_physique_id']);
            if ($boite) {
                $fichier->setBoitePhysique($boite);
            } else {
                $this->warnings[] = "Ligne $lineNumber : Boîte physique {$data['boite_physique_id']} introuvable";
            }
        }

        // Dates (optionnelles)
        if (!empty($data['date_debut'])) {
            try {
                $fichier->setDateDebut(new \DateTime($data['date_debut']));
            } catch (\Exception $e) {
                $this->warnings[] = "Ligne $lineNumber : Format de date_debut invalide";
            }
        }

        if (!empty($data['date_fin'])) {
            try {
                $fichier->setDateFin(new \DateTime($data['date_fin']));
            } catch (\Exception $e) {
                $this->warnings[] = "Ligne $lineNumber : Format de date_fin invalide";
            }
        }

        // Typologie (optionnelle)
        if (!empty($data['typologie_documentaire'])) {
            $fichier->setTypologieDocumentaire($data['typologie_documentaire']);
        }

        // Règle de rétention (optionnelle)
        if (!empty($data['regle_retention_id'])) {
            $regle = $this->regleRetentionRepo->find((int)$data['regle_retention_id']);
            if ($regle) {
                $fichier->setRegleRetention($regle);
            } else {
                $this->warnings[] = "Ligne $lineNumber : Règle de rétention {$data['regle_retention_id']} introuvable";
            }
        }

        // Calcul du checksum SHA-256 si le fichier existe physiquement
        $cheminAcces = $fichier->getCheminAcces();
        if (!empty($cheminAcces) && file_exists($cheminAcces)) {
            $this->fileIntegrityService->calculateAndStoreChecksum($fichier, $cheminAcces);
        } elseif (!empty($cheminAcces)) {
            $this->warnings[] = "Ligne $lineNumber : Fichier physique '$cheminAcces' introuvable, checksum non calculé";
        }

        return $fichier;
    }

    /**
     * Construit le résultat de l'import
     */
    private function buildResult(int $successCount): array
    {
        return [
            'success' => $successCount,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
