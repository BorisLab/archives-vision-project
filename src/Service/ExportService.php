<?php

namespace App\Service;

use App\Entity\Dossier;
use App\Entity\Fichier;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExportService
{
    /**
     * Exporte une liste de dossiers vers Excel ou CSV
     * 
     * @param array $dossiers Liste des dossiers à exporter
     * @param string $format Format d'export : 'xlsx' ou 'csv'
     * @return string Chemin du fichier généré
     */
    public function exportDossiers(array $dossiers, string $format = 'xlsx'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dossiers');

        // En-têtes
        $headers = [
            'ID',
            'Libellé',
            'Format',
            'Tags',
            'Département',
            'Créateur',
            'Dossier Parent',
            'Statut',
            'Date Début',
            'Date Fin',
            'Typologie',
            'Règle Rétention',
            'Date Création',
        ];

        // Style des en-têtes
        $headerRow = 1;
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $col++;
        }

        // Données
        $row = 2;
        /** @var Dossier $dossier */
        foreach ($dossiers as $dossier) {
            $sheet->setCellValue('A' . $row, $dossier->getDossierId());
            $sheet->setCellValue('B' . $row, $dossier->getLibelleDossier());
            $sheet->setCellValue('C' . $row, $dossier->getFormat());
            $sheet->setCellValue('D' . $row, $dossier->getTags());
            $sheet->setCellValue('E' . $row, $dossier->getDepartement()?->getLibelleDep());
            $sheet->setCellValue('F' . $row, $dossier->getUtilisateur()?->getEmail());
            $sheet->setCellValue('G' . $row, $dossier->getDossierParent()?->getLibelleDossier());
            $sheet->setCellValue('H' . $row, $dossier->isStatut() ? 'Actif' : 'Inactif');
            $sheet->setCellValue('I' . $row, $dossier->getDateDebut()?->format('Y-m-d'));
            $sheet->setCellValue('J' . $row, $dossier->getDateFin()?->format('Y-m-d'));
            $sheet->setCellValue('K' . $row, $dossier->getTypologieDocumentaire());
            $sheet->setCellValue('L' . $row, $dossier->getRegleRetention()?->getNom());
            $sheet->setCellValue('M' . $row, $dossier->getDateCreation()?->format('Y-m-d H:i:s'));
            $row++;
        }

        // Auto-dimensionner les colonnes
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Générer le fichier
        $filename = 'export_dossiers_' . date('Y-m-d_His') . '.' . $format;
        $filepath = sys_get_temp_dir() . '/' . $filename;

        if ($format === 'csv') {
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);
        } else {
            $writer = new Xlsx($spreadsheet);
        }

        $writer->save($filepath);

        return $filepath;
    }

    /**
     * Exporte une liste de fichiers vers Excel ou CSV
     * 
     * @param array $fichiers Liste des fichiers à exporter
     * @param string $format Format d'export : 'xlsx' ou 'csv'
     * @return string Chemin du fichier généré
     */
    public function exportFichiers(array $fichiers, string $format = 'xlsx'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Fichiers');

        // En-têtes
        $headers = [
            'ID',
            'Libellé',
            'Type',
            'Format',
            'Tags',
            'Dossier',
            'Département',
            'Boîte Physique',
            'Statut',
            'Date Début',
            'Date Fin',
            'Typologie',
            'Règle Rétention',
            'Chemin Accès',
            'Date Création',
        ];

        // Style des en-têtes
        $headerRow = 1;
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '70AD47'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $col++;
        }

        // Données
        $row = 2;
        /** @var Fichier $fichier */
        foreach ($fichiers as $fichier) {
            $sheet->setCellValue('A' . $row, $fichier->getFichierId());
            $sheet->setCellValue('B' . $row, $fichier->getLibelleFichier());
            $sheet->setCellValue('C' . $row, $fichier->getType());
            $sheet->setCellValue('D' . $row, $fichier->getFormat());
            $sheet->setCellValue('E' . $row, $fichier->getTags());
            $sheet->setCellValue('F' . $row, $fichier->getDossier()?->getLibelleDossier());
            $sheet->setCellValue('G' . $row, $fichier->getDossier()?->getDepartement()?->getLibelleDep());
            $sheet->setCellValue('H' . $row, $fichier->getBoitePhysique()?->getCodeBoite());
            $sheet->setCellValue('I' . $row, $fichier->isStatut() ? 'Disponible' : 'Indisponible');
            $sheet->setCellValue('J' . $row, $fichier->getDateDebut()?->format('Y-m-d'));
            $sheet->setCellValue('K' . $row, $fichier->getDateFin()?->format('Y-m-d'));
            $sheet->setCellValue('L' . $row, $fichier->getTypologieDocumentaire());
            $sheet->setCellValue('M' . $row, $fichier->getRegleRetention()?->getNom());
            $sheet->setCellValue('N' . $row, $fichier->getCheminAcces());
            $sheet->setCellValue('O' . $row, $fichier->getDateCreation()?->format('Y-m-d H:i:s'));
            $row++;
        }

        // Auto-dimensionner les colonnes
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Générer le fichier
        $filename = 'export_fichiers_' . date('Y-m-d_His') . '.' . $format;
        $filepath = sys_get_temp_dir() . '/' . $filename;

        if ($format === 'csv') {
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);
        } else {
            $writer = new Xlsx($spreadsheet);
        }

        $writer->save($filepath);

        return $filepath;
    }

    /**
     * Génère un template d'import pour les dossiers
     */
    public function generateDossiersTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Dossiers');

        // En-têtes avec instructions
        $headers = [
            'libelle_dossier' => 'Libellé du dossier (obligatoire)',
            'format' => 'Format (Numérique/Physique)',
            'tags' => 'Tags séparés par virgules',
            'departement_id' => 'ID du département (obligatoire)',
            'dossier_parent_id' => 'ID du dossier parent (optionnel)',
            'statut' => 'Statut (1=actif, 0=inactif)',
            'date_debut' => 'Date début (YYYY-MM-DD)',
            'date_fin' => 'Date fin (YYYY-MM-DD)',
            'typologie_documentaire' => 'Typologie documentaire',
            'regle_retention_id' => 'ID de la règle de rétention (optionnel)',
        ];

        $col = 'A';
        foreach ($headers as $field => $description) {
            $sheet->setCellValue($col . '1', $field);
            $sheet->setCellValue($col . '2', $description);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '2')->getFont()->setItalic(true);
            $col++;
        }

        // Ligne d'exemple
        $sheet->setCellValue('A3', 'Mon Dossier Test');
        $sheet->setCellValue('B3', 'Numérique');
        $sheet->setCellValue('C3', 'tag1,tag2,tag3');
        $sheet->setCellValue('D3', '1');
        $sheet->setCellValue('E3', '');
        $sheet->setCellValue('F3', '1');
        $sheet->setCellValue('G3', '2024-01-01');
        $sheet->setCellValue('H3', '2024-12-31');
        $sheet->setCellValue('I3', 'Administratif');
        $sheet->setCellValue('J3', '');

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'template_import_dossiers.xlsx';
        $filepath = sys_get_temp_dir() . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }

    /**
     * Génère un template d'import pour les fichiers
     */
    public function generateFichiersTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Fichiers');

        // En-têtes avec instructions
        $headers = [
            'libelle_fichier' => 'Libellé du fichier (obligatoire)',
            'type' => 'Type (obligatoire)',
            'format' => 'Format (Numérique/Physique)',
            'tags' => 'Tags séparés par virgules',
            'dossier_id' => 'ID du dossier (obligatoire)',
            'boite_physique_id' => 'ID de la boîte physique (optionnel)',
            'statut' => 'Statut (1=disponible, 0=indisponible)',
            'date_debut' => 'Date début (YYYY-MM-DD)',
            'date_fin' => 'Date fin (YYYY-MM-DD)',
            'typologie_documentaire' => 'Typologie documentaire',
            'regle_retention_id' => 'ID de la règle de rétention (optionnel)',
            'chemin_acces' => 'Chemin d\'accès (pour fichiers numériques)',
        ];

        $col = 'A';
        foreach ($headers as $field => $description) {
            $sheet->setCellValue($col . '1', $field);
            $sheet->setCellValue($col . '2', $description);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '2')->getFont()->setItalic(true);
            $col++;
        }

        // Ligne d'exemple
        $sheet->setCellValue('A3', 'Mon Fichier Test');
        $sheet->setCellValue('B3', 'Document');
        $sheet->setCellValue('C3', 'Numérique');
        $sheet->setCellValue('D3', 'tag1,tag2');
        $sheet->setCellValue('E3', '1');
        $sheet->setCellValue('F3', '');
        $sheet->setCellValue('G3', '1');
        $sheet->setCellValue('H3', '2024-01-01');
        $sheet->setCellValue('I3', '2024-12-31');
        $sheet->setCellValue('J3', 'Contrat');
        $sheet->setCellValue('K3', '');
        $sheet->setCellValue('L3', '/uploads/documents/fichier.pdf');

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'template_import_fichiers.xlsx';
        $filepath = sys_get_temp_dir() . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }
}
