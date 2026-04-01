<?php

namespace App\Service;

use App\Entity\DemandeAcces;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BordereauPretService
{
    private string $uploadsDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadsDir = $params->get('kernel.project_dir') . '/public/uploads/bordereaux';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0755, true);
        }
    }

    /**
     * Génère un bordereau de prêt PDF pour une demande d'accès
     * UNIQUEMENT si le document est physique
     * 
     * @param DemandeAcces $demandeAcces La demande d'accès approuvée
     * @return string|null Chemin relatif du PDF généré ou null si document numérique
     */
    public function generateBordereau(DemandeAcces $demandeAcces): ?string
    {
        // Vérifier si le document est physique
        if (!$this->isPhysicalDocument($demandeAcces)) {
            return null; // Pas de bordereau pour les documents numériques
        }

        // Générer le HTML du bordereau
        $html = $this->generateHtml($demandeAcces);

        // Configurer Dompdf
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer nom de fichier unique
        $fileName = 'bordereau_' . $demandeAcces->getId() . '_' . time() . '.pdf';
        $filePath = $this->uploadsDir . '/' . $fileName;

        // Sauvegarder le PDF
        file_put_contents($filePath, $dompdf->output());

        // Retourner le chemin relatif
        return '/uploads/bordereaux/' . $fileName;
    }

    /**
     * Vérifie si le document demandé est physique
     */
    private function isPhysicalDocument(DemandeAcces $demandeAcces): bool
    {
        $dossier = $demandeAcces->getDossier();
        $fichier = $demandeAcces->getFichier();

        if ($fichier) {
            return $fichier->getFormat() === 'Physique';
        }

        if ($dossier) {
            return $dossier->getFormat() === 'Physique';
        }

        return false;
    }

    /**
     * Génère le HTML du bordereau de prêt
     */
    private function generateHtml(DemandeAcces $demandeAcces): string
    {
        $emprunteur = $demandeAcces->getUtilisateur();
        $approbateur = $demandeAcces->getApprobateur();
        $dossier = $demandeAcces->getDossier();
        $fichier = $demandeAcces->getFichier();
        
        $dateEmission = (new \DateTime())->format('d/m/Y');
        $dateExpiration = $demandeAcces->getExpiration() 
            ? $demandeAcces->getExpiration()->format('d/m/Y') 
            : 'Non définie';

        // Informations du document
        if ($fichier) {
            $typeDocument = 'Fichier physique';
            $reference = $fichier->getLibelleFichier();
            $cote = $fichier->getBoitePhysique() 
                ? $fichier->getBoitePhysique()->getNumeroBoite() 
                : 'Non définie';
            $localisation = $fichier->getBoitePhysique() 
                ? $fichier->getBoitePhysique()->getLocalisation() 
                : 'Non définie';
        } else {
            $typeDocument = 'Dossier physique';
            $reference = $dossier->getLibelleDossier();
            $cote = 'N/A';
            $localisation = 'Salle des archives';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bordereau de Prêt</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #1e40af;
            font-size: 20pt;
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            color: #64748b;
            font-size: 10pt;
            margin: 5px 0;
        }
        .numero-bordereau {
            text-align: right;
            font-size: 10pt;
            color: #64748b;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 25px;
            padding: 15px;
            background: #f8fafc;
            border-left: 4px solid #2563eb;
        }
        .section-title {
            font-size: 13pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            color: #475569;
            padding: 5px 15px 5px 0;
            width: 40%;
        }
        .info-value {
            display: table-cell;
            color: #1e293b;
            padding: 5px 0;
        }
        .conditions {
            margin-top: 30px;
            padding: 15px;
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 5px;
        }
        .conditions h3 {
            color: #92400e;
            font-size: 12pt;
            margin: 0 0 10px 0;
        }
        .conditions ul {
            margin: 0;
            padding-left: 20px;
            font-size: 10pt;
        }
        .conditions li {
            margin-bottom: 5px;
            color: #78350f;
        }
        .signatures {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 10px;
            text-align: center;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #94a3b8;
            padding-top: 5px;
            font-size: 10pt;
            color: #64748b;
        }
        .footer {
            margin-top: 50px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            font-size: 9pt;
            color: #94a3b8;
        }
        .alert {
            background: #fee2e2;
            border: 2px solid #ef4444;
            padding: 12px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 10pt;
            color: #991b1b;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏛️ Bordereau de Prêt de Document</h1>
        <p>Service des Archives - Système de Gestion Archivistique</p>
    </div>

    <div class="numero-bordereau">
        <strong>N° Bordereau:</strong> BP-{$demandeAcces->getId()}<br>
        <strong>Date d'émission:</strong> {$dateEmission}
    </div>

    <div class="section">
        <div class="section-title">📄 Informations du Document</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Type de document :</div>
                <div class="info-value">{$typeDocument}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Référence :</div>
                <div class="info-value">{$reference}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Cote :</div>
                <div class="info-value">{$cote}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Localisation :</div>
                <div class="info-value">{$localisation}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">👤 Informations de l'Emprunteur</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nom complet :</div>
                <div class="info-value">{$emprunteur->getNom()} {$emprunteur->getPrenom()}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email :</div>
                <div class="info-value">{$emprunteur->getEmail()}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Département :</div>
                <div class="info-value">{$emprunteur->getDepartement()->getLibelleDep()}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">📅 Informations du Prêt</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Date de prêt :</div>
                <div class="info-value">{$dateEmission}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de retour prévue :</div>
                <div class="info-value">{$dateExpiration}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Approuvé par :</div>
                <div class="info-value">{$approbateur->getNom()} {$approbateur->getPrenom()} (Archiviste)</div>
            </div>
        </div>
    </div>

    <div class="conditions">
        <h3>⚠️ Conditions d'utilisation et de restitution</h3>
        <ul>
            <li>L'emprunteur s'engage à restituer le document dans l'état où il l'a reçu</li>
            <li>Toute reproduction totale ou partielle est interdite sans autorisation</li>
            <li>Le document doit être retourné à la date prévue sous peine de sanctions</li>
            <li>En cas de perte ou de dégradation, l'emprunteur en assume la responsabilité</li>
            <li>Le document ne peut être prêté à un tiers sans autorisation</li>
            <li>Manipulation avec précaution (pas de nourriture, boisson à proximité)</li>
        </ul>
    </div>

    <div class="alert">
        ⚠️ IMPORTANT : Ce bordereau doit être conservé par l'emprunteur et présenté lors de la restitution
    </div>

    <div class="signatures">
        <div class="signature-box">
            <strong>L'Archiviste</strong>
            <div class="signature-line">
                {$approbateur->getNom()} {$approbateur->getPrenom()}<br>
                Date: {$dateEmission}
            </div>
        </div>
        <div class="signature-box">
            <strong>L'Emprunteur</strong>
            <div class="signature-line">
                {$emprunteur->getNom()} {$emprunteur->getPrenom()}<br>
                Signature:
            </div>
        </div>
    </div>

    <div class="footer">
        Document généré automatiquement le {$dateEmission}<br>
        Ce bordereau fait foi en cas de litige
    </div>
</body>
</html>
HTML;
    }
}
