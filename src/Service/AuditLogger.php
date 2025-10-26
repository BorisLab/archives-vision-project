<?php

namespace App\Service;

use App\Entity\AuditLog;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class AuditLogger
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private RequestStack $requestStack;
    private ?Security $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $auditLogger,
        RequestStack $requestStack,
        ?Security $security = null
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $auditLogger;
        $this->requestStack = $requestStack;
        $this->security = $security;
    }

    /**
     * Log an action to database and file
     *
     * @param string $action Action performed (e.g., 'view', 'download', 'create', 'update', 'delete')
     * @param string $entityType Entity type (e.g., 'Dossier', 'Fichier', 'DemandeAcces')
     * @param int|null $entityId Entity ID
     * @param array $details Additional details to log
     * @return void
     */
    public function log(string $action, string $entityType, ?int $entityId = null, array $details = []): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->security ? $this->security->getUser() : null;

        // Create audit log entry
        $auditLog = new AuditLog();
        $auditLog->setAction($action);
        $auditLog->setEntityType($entityType);
        $auditLog->setEntityId($entityId);

        // User information
        if ($user instanceof Utilisateur) {
            $auditLog->setUserId($user->getId());
            $auditLog->setUserEmail($user->getEmail());
        }

        // Request information
        if ($request) {
            $auditLog->setIpAddress($this->getClientIp($request));
            $auditLog->setUserAgent($request->headers->get('User-Agent'));
        }

        // Additional details
        if (!empty($details)) {
            $auditLog->setDetails($details);
        }

        // Persist to database
        try {
            $this->entityManager->persist($auditLog);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            // If DB fails, at least log to file
            $this->logger->error('Failed to persist audit log to database', [
                'exception' => $e->getMessage(),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);
        }

        // Log to file (Monolog audit channel)
        $this->logger->info(sprintf(
            '[%s] %s on %s #%s by user #%s (%s) from IP %s',
            strtoupper($action),
            $entityType,
            $entityId ?? 'N/A',
            $user ? $user->getId() : 'anonymous',
            $user ? $user->getEmail() : 'anonymous',
            $request ? $this->getClientIp($request) : 'N/A'
        ), [
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $user ? $user->getId() : null,
            'user_email' => $user ? $user->getEmail() : null,
            'ip_address' => $request ? $this->getClientIp($request) : null,
            'user_agent' => $request ? $request->headers->get('User-Agent') : null,
            'details' => $details,
        ]);
    }

    /**
     * Log document view
     */
    public function logView(string $entityType, int $entityId, array $details = []): void
    {
        $this->log('view', $entityType, $entityId, $details);
    }

    /**
     * Log document download
     */
    public function logDownload(string $entityType, int $entityId, array $details = []): void
    {
        $this->log('download', $entityType, $entityId, $details);
    }

    /**
     * Log document creation
     */
    public function logCreate(string $entityType, int $entityId, array $details = []): void
    {
        $this->log('create', $entityType, $entityId, $details);
    }

    /**
     * Log document update
     */
    public function logUpdate(string $entityType, int $entityId, array $details = []): void
    {
        $this->log('update', $entityType, $entityId, $details);
    }

    /**
     * Log document deletion
     */
    public function logDelete(string $entityType, int $entityId, array $details = []): void
    {
        $this->log('delete', $entityType, $entityId, $details);
    }

    /**
     * Log access request approval
     */
    public function logApprove(string $entityType, int $entityId, array $details = []): void
    {
        $this->log('approve', $entityType, $entityId, $details);
    }

    /**
     * Log access request rejection
     */
    public function logReject(string $entityType, int $entityId, array $details = []): void
    {
        $this->log('reject', $entityType, $entityId, $details);
    }

    /**
     * Get real client IP address (handles proxies)
     */
    private function getClientIp($request): string
    {
        $ipAddress = $request->getClientIp();
        
        // Check for proxy headers
        if ($request->server->has('HTTP_X_FORWARDED_FOR')) {
            $ipAddress = $request->server->get('HTTP_X_FORWARDED_FOR');
        } elseif ($request->server->has('HTTP_CLIENT_IP')) {
            $ipAddress = $request->server->get('HTTP_CLIENT_IP');
        }

        // If multiple IPs (proxy chain), get the first one
        if (strpos($ipAddress, ',') !== false) {
            $ipAddress = trim(explode(',', $ipAddress)[0]);
        }

        return $ipAddress ?? '0.0.0.0';
    }
}
