<?php

namespace App\Controller;

use App\Entity\AuditLog;
use App\Entity\Message;
use App\Entity\Departement;
use App\Entity\Utilisateur;
use App\Entity\Notification;
use App\Entity\StatutMessage;
use App\Entity\TypeNotification;
use App\Repository\AuditLogRepository;
use App\Service\AuditLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuditController extends AbstractController
{
    #[Route('/admin/audit', name: 'app_admin_audit')]
    #[IsGranted("ROLE_ADMIN")]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);
        $nbrDeps = $entityManager->getRepository(Departement::class)->count([]);
        $nbrUsers = $entityManager->getRepository(Utilisateur::class)->count([]);
        $reqNotifs = $entityManager->getRepository(Notification::class)->findBy(['type' => [TypeNotification::INFO_REPONSE,TypeNotification::APPROB_REPONSE,TypeNotification::REJET_REPONSE,TypeNotification::REVOC_REPONSE]]);

        foreach($reqNotifs as $reqN){
            $reqN->archiviste = $entityManager->getRepository(Utilisateur::class)->find($reqN->getDemandeAcces()->getArchivisteId());
        }

        //dd($reqNotifs);

        $adminAuditResponse = $this->render('administrator/audit.html.twig', [
            'admin_audit' => 'AdminAuditPage',
            'nbr_msgs_unread' => $nbrMsgsUnread,
            'nbr_deps' => $nbrDeps,
            'nbr_users' => $nbrUsers,
            'notifs' => $reqNotifs
        ]);

        $adminAuditResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]); 

        return $adminAuditResponse;
    }

    #[Route('/admin/audit/logs', name: 'app_admin_audit_logs')]
    #[IsGranted("ROLE_ADMIN")]
    public function auditLogs(Request $request, AuditLogRepository $auditLogRepository, EntityManagerInterface $entityManager): Response
    {
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);
        
        // Get filters from request
        $filters = [];
        $action = $request->query->get('action');
        $entityType = $request->query->get('entity_type');
        $userId = $request->query->get('user_id');
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');
        
        if ($action) {
            $filters['action'] = $action;
        }
        if ($entityType) {
            $filters['entity_type'] = $entityType;
        }
        if ($userId) {
            $filters['user_id'] = (int)$userId;
        }
        if ($dateFrom) {
            $filters['date_from'] = new \DateTime($dateFrom);
        }
        if ($dateTo) {
            $filters['date_to'] = new \DateTime($dateTo);
        }

        // Pagination
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        // Get logs and count
        $logs = $auditLogRepository->findWithFilters($filters, $limit, $offset);
        $totalLogs = $auditLogRepository->countWithFilters($filters);
        $totalPages = ceil($totalLogs / $limit);

        // Get all users for filter dropdown
        $users = $entityManager->getRepository(Utilisateur::class)->findAll();

        // Get statistics
        $stats = $auditLogRepository->getStatsByAction();

        return $this->render('administrator/audit_logs.html.twig', [
            'admin_audit_logs' => 'AdminAuditLogsPage',
            'logs' => $logs,
            'users' => $users,
            'stats' => $stats,
            'filters' => $filters,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_logs' => $totalLogs,
            'nbr_msgs_unread' => $nbrMsgsUnread,
        ]);
    }
}
