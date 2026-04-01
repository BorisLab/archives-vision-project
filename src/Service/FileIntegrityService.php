<?php

namespace App\Service;

use App\Entity\Fichier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service de gestion de l'intégrité des fichiers via checksums SHA-256
 */
class FileIntegrityService
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Calcule et enregistre le checksum SHA-256 d'un fichier
     *
     * @param Fichier $fichier L'entité fichier
     * @param string $filePath Le chemin physique du fichier sur le disque
     * @return string|null Le checksum calculé ou null en cas d'erreur
     */
    public function calculateAndStoreChecksum(Fichier $fichier, string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            $this->logger->error('Fichier introuvable pour calcul checksum', [
                'fichier_id' => $fichier->getFichierId(),
                'path' => $filePath
            ]);
            return null;
        }

        try {
            $checksum = hash_file('sha256', $filePath);
            
            if ($checksum === false) {
                throw new \RuntimeException('Échec du calcul du hash');
            }

            $fichier->setChecksumSha256($checksum);
            $this->entityManager->flush();

            $this->logger->info('Checksum SHA-256 calculé et enregistré', [
                'fichier_id' => $fichier->getFichierId(),
                'checksum' => $checksum
            ]);

            return $checksum;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du calcul du checksum', [
                'fichier_id' => $fichier->getFichierId(),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Vérifie l'intégrité d'un fichier en comparant son checksum actuel avec celui enregistré
     *
     * @param Fichier $fichier L'entité fichier
     * @param string $filePath Le chemin physique du fichier sur le disque
     * @return bool True si le fichier est intègre, false sinon
     */
    public function verifyIntegrity(Fichier $fichier, string $filePath): bool
    {
        $storedChecksum = $fichier->getChecksumSha256();

        if ($storedChecksum === null) {
            $this->logger->warning('Aucun checksum enregistré pour ce fichier', [
                'fichier_id' => $fichier->getFichierId()
            ]);
            return false;
        }

        if (!file_exists($filePath)) {
            $this->logger->error('Fichier introuvable pour vérification', [
                'fichier_id' => $fichier->getFichierId(),
                'path' => $filePath
            ]);
            return false;
        }

        try {
            $currentChecksum = hash_file('sha256', $filePath);

            if ($currentChecksum === false) {
                throw new \RuntimeException('Échec du calcul du hash');
            }

            $isValid = ($currentChecksum === $storedChecksum);

            if (!$isValid) {
                $this->logger->critical('INTÉGRITÉ COMPROMISE - Checksum invalide', [
                    'fichier_id' => $fichier->getFichierId(),
                    'expected' => $storedChecksum,
                    'actual' => $currentChecksum
                ]);
            }

            return $isValid;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la vérification du checksum', [
                'fichier_id' => $fichier->getFichierId(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Recalcule les checksums de tous les fichiers sans checksum
     *
     * @param string $uploadDirectory Répertoire racine des uploads
     * @return array Statistiques de l'opération ['success' => int, 'failed' => int, 'skipped' => int]
     */
    public function batchCalculateChecksums(string $uploadDirectory): array
    {
        $fichierRepo = $this->entityManager->getRepository(Fichier::class);
        $fichiers = $fichierRepo->createQueryBuilder('f')
            ->where('f.checksum_sha256 IS NULL')
            ->andWhere('f.chemin_acces IS NOT NULL')
            ->getQuery()
            ->getResult();

        $stats = ['success' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($fichiers as $fichier) {
            $filePath = $uploadDirectory . '/' . $fichier->getCheminAcces();

            if (!file_exists($filePath)) {
                $stats['skipped']++;
                $this->logger->warning('Fichier physique introuvable', [
                    'fichier_id' => $fichier->getFichierId(),
                    'path' => $filePath
                ]);
                continue;
            }

            $checksum = $this->calculateAndStoreChecksum($fichier, $filePath);

            if ($checksum !== null) {
                $stats['success']++;
            } else {
                $stats['failed']++;
            }
        }

        $this->logger->info('Batch checksum terminé', $stats);

        return $stats;
    }
}
