<?php

namespace App\Service;

use App\Entity\DemandeDestruction;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Génère un PDF d'autorisation de destruction
     */
    public function generateAuthorizationPdf(DemandeDestruction $demande): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        
        // Génération du contenu HTML
        $html = $this->getAuthorizationHtml($demande);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Nom du fichier
        $fileName = 'autorisation_destruction_' . $demande->getId() . '_' . date('YmdHis') . '.pdf';
        $filePath = $this->projectDir . '/public/uploads/destruction_pdfs/' . $fileName;
        
        // Créer le dossier s'il n'existe pas
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        
        // Sauvegarder le PDF
        file_put_contents($filePath, $dompdf->output());
        
        // Retourner le chemin relatif
        return 'destruction_pdfs/' . $fileName;
    }

    /**
     * Génère le contenu HTML du PDF d'autorisation
     */
    private function getAuthorizationHtml(DemandeDestruction $demande): string
    {
        $dateTraitement = $demande->getDateTraitement() ? $demande->getDateTraitement()->format('d/m/Y à H:i') : '';
        $approbateur = $demande->getApprobateur() ? $demande->getApprobateur()->getNom() . ' ' . $demande->getApprobateur()->getPrenom() : '';
        $demandeur = $demande->getDemandeur()->getNom() . ' ' . $demande->getDemandeur()->getPrenom();

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Autorisation de Destruction</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #0066cc;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #0066cc;
            font-size: 24pt;
            margin: 0;
        }
        .header p {
            color: #666;
            margin: 5px 0;
        }
        .content {
            margin: 30px 0;
        }
        .info-block {
            background: #f5f5f5;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #0066cc;
        }
        .info-block h3 {
            margin-top: 0;
            color: #0066cc;
            font-size: 14pt;
        }
        .info-row {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 180px;
        }
        .signature-block {
            margin-top: 60px;
            page-break-inside: avoid;
        }
        .signature-line {
            margin-top: 80px;
            border-top: 1px solid #333;
            width: 300px;
            text-align: center;
            padding-top: 10px;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 9pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>AUTORISATION DE DESTRUCTION</h1>
        <p>Document officiel - Archives institutionnelles</p>
        <p>N° {$demande->getId()} - Émis le {$dateTraitement}</p>
    </div>

    <div class="content">
        <div class="info-block">
            <h3>Informations sur l'entité à détruire</h3>
            <div class="info-row">
                <span class="label">Type d'entité :</span>
                <span>{$demande->getTypeEntite()}</span>
            </div>
            <div class="info-row">
                <span class="label">Identifiant :</span>
                <span>#{$demande->getEntiteId()}</span>
            </div>
            <div class="info-row">
                <span class="label">Libellé :</span>
                <span><strong>{$demande->getLibelleEntite()}</strong></span>
            </div>
        </div>

        <div class="info-block">
            <h3>Détails de la demande</h3>
            <div class="info-row">
                <span class="label">Demandeur :</span>
                <span>{$demandeur}</span>
            </div>
            <div class="info-row">
                <span class="label">Date de demande :</span>
                <span>{$demande->getDateDemande()->format('d/m/Y à H:i')}</span>
            </div>
            <div class="info-row">
                <span class="label">Justification :</span>
            </div>
            <div style="margin-left: 20px; margin-top: 10px; font-style: italic;">
                {$demande->getJustification()}
            </div>
        </div>

        <div class="info-block">
            <h3>Approbation</h3>
            <div class="info-row">
                <span class="label">Approuvé par :</span>
                <span>{$approbateur}</span>
            </div>
            <div class="info-row">
                <span class="label">Date d'approbation :</span>
                <span>{$dateTraitement}</span>
            </div>
            <div class="info-row">
                <span class="label">Statut :</span>
                <span style="color: green; font-weight: bold;">APPROUVÉE</span>
            </div>
        </div>

        <div class="warning">
            <strong>⚠ ATTENTION :</strong> Ce document autorise la destruction définitive de l'entité mentionnée ci-dessus.
            Cette action est irréversible et doit être exécutée conformément aux procédures en vigueur.
        </div>

        <div class="signature-block">
            <p><strong>Pour valeur juridique et archivage</strong></p>
            <div class="signature-line">
                Signature de l'approbateur<br>
                {$approbateur}
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Document généré automatiquement le {$dateTraitement} | Référence: AUTH-DEST-{$demande->getId()}</p>
        <p>Ce document doit être conservé dans les archives administratives pendant une durée minimale de 10 ans</p>
    </div>
</body>
</html>
HTML;
    }
}
