<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Service de scan antivirus avec ClamAV
 * 
 * Ce service permet de scanner les fichiers uploadés pour détecter les virus et malwares.
 * Il utilise ClamAV via une socket TCP ou UNIX selon la configuration.
 */
class AntivirusService
{
    private LoggerInterface $logger;
    private ParameterBagInterface $params;
    private bool $enabled;
    private string $host;
    private int $port;
    private int $timeout;

    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->logger = $logger;
        $this->params = $params;
        
        // Configuration ClamAV depuis les variables d'environnement
        $this->enabled = $params->get('clamav.enabled') ?? false;
        $this->host = $params->get('clamav.host') ?? 'localhost';
        $this->port = $params->get('clamav.port') ?? 3310;
        $this->timeout = $params->get('clamav.timeout') ?? 30;
    }

    /**
     * Vérifie si le service antivirus est activé
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Vérifie si ClamAV est disponible et opérationnel
     */
    public function isAvailable(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $socket = $this->getSocket();
            if (!$socket) {
                return false;
            }

            fwrite($socket, "PING\n");
            $response = trim(fgets($socket));
            fclose($socket);

            return $response === 'PONG';
        } catch (\Exception $e) {
            $this->logger->warning('ClamAV ping failed', [
                'error' => $e->getMessage(),
                'host' => $this->host,
                'port' => $this->port,
            ]);
            return false;
        }
    }

    /**
     * Scanne un fichier pour détecter les virus
     * 
     * @param string $filePath Chemin absolu du fichier à scanner
     * @return array ['clean' => bool, 'virus' => string|null, 'error' => string|null]
     */
    public function scanFile(string $filePath): array
    {
        // Si le service est désactivé, on considère le fichier comme propre
        if (!$this->enabled) {
            $this->logger->info('ClamAV disabled, skipping scan', ['file' => $filePath]);
            return [
                'clean' => true,
                'virus' => null,
                'error' => null,
                'skipped' => true,
            ];
        }

        // Vérifier que le fichier existe
        if (!file_exists($filePath)) {
            $error = "File not found: $filePath";
            $this->logger->error('Antivirus scan error', ['error' => $error]);
            return [
                'clean' => false,
                'virus' => null,
                'error' => $error,
            ];
        }

        try {
            $socket = $this->getSocket();
            if (!$socket) {
                throw new \RuntimeException('Could not connect to ClamAV daemon');
            }

            // Utiliser la commande INSTREAM pour scanner le contenu du fichier
            fwrite($socket, "zINSTREAM\0");

            // Lire le fichier par chunks et l'envoyer à ClamAV
            $handle = fopen($filePath, 'rb');
            if (!$handle) {
                fclose($socket);
                throw new \RuntimeException("Could not open file: $filePath");
            }

            while (!feof($handle)) {
                $chunk = fread($handle, 8192);
                if ($chunk === false) {
                    break;
                }
                
                $size = pack('N', strlen($chunk));
                fwrite($socket, $size);
                fwrite($socket, $chunk);
            }

            fclose($handle);

            // Envoyer un chunk de taille 0 pour signaler la fin
            fwrite($socket, pack('N', 0));

            // Lire la réponse
            $response = trim(fgets($socket));
            fclose($socket);

            // Parser la réponse
            $result = $this->parseResponse($response, $filePath);

            // Logger le résultat
            if (!$result['clean']) {
                $this->logger->critical('VIRUS DETECTED', [
                    'file' => $filePath,
                    'virus' => $result['virus'],
                    'response' => $response,
                ]);
            } else {
                $this->logger->info('File scanned successfully', [
                    'file' => basename($filePath),
                    'clean' => true,
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->logger->error('Antivirus scan failed', [
                'file' => $filePath,
                'error' => $error,
                'host' => $this->host,
                'port' => $this->port,
            ]);

            return [
                'clean' => false,
                'virus' => null,
                'error' => $error,
            ];
        }
    }

    /**
     * Scanne plusieurs fichiers
     * 
     * @param array $filePaths Liste des chemins de fichiers à scanner
     * @return array Résultats indexés par chemin de fichier
     */
    public function scanMultipleFiles(array $filePaths): array
    {
        $results = [];
        
        foreach ($filePaths as $filePath) {
            $results[$filePath] = $this->scanFile($filePath);
        }

        return $results;
    }

    /**
     * Obtient la version de ClamAV
     */
    public function getVersion(): ?string
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $socket = $this->getSocket();
            if (!$socket) {
                return null;
            }

            fwrite($socket, "VERSION\n");
            $version = trim(fgets($socket));
            fclose($socket);

            return $version;
        } catch (\Exception $e) {
            $this->logger->warning('Could not get ClamAV version', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtient des statistiques sur ClamAV
     */
    public function getStats(): array
    {
        return [
            'enabled' => $this->enabled,
            'available' => $this->isAvailable(),
            'host' => $this->host,
            'port' => $this->port,
            'version' => $this->getVersion(),
        ];
    }

    /**
     * Crée une connexion socket vers ClamAV
     */
    private function getSocket()
    {
        $socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        
        if (!$socket) {
            $this->logger->error('Could not connect to ClamAV', [
                'host' => $this->host,
                'port' => $this->port,
                'errno' => $errno,
                'errstr' => $errstr,
            ]);
            return false;
        }

        stream_set_timeout($socket, $this->timeout);
        return $socket;
    }

    /**
     * Parse la réponse de ClamAV
     */
    private function parseResponse(string $response, string $filePath): array
    {
        // Format de réponse ClamAV: "stream: OK" ou "stream: Virus.Name FOUND"
        if (strpos($response, 'OK') !== false) {
            return [
                'clean' => true,
                'virus' => null,
                'error' => null,
            ];
        }

        if (strpos($response, 'FOUND') !== false) {
            // Extraire le nom du virus
            preg_match('/stream: (.+) FOUND/', $response, $matches);
            $virusName = $matches[1] ?? 'Unknown virus';

            return [
                'clean' => false,
                'virus' => $virusName,
                'error' => null,
            ];
        }

        // Erreur ou réponse inattendue
        return [
            'clean' => false,
            'virus' => null,
            'error' => "Unexpected response: $response",
        ];
    }
}
