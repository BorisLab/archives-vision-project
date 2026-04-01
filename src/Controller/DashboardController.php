<?php

namespace App\Controller;

use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Notification;
use App\Entity\Message;
use App\Entity\StatutNotification;
use App\Entity\StatutMessage;
use App\Entity\NiveauAccesNotification;
use Symfony\Component\Mercure\Authorization;


class DashboardController extends AbstractController
{
    private StatisticsService $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    #[Route('/admin/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, EntityManagerInterface $entityManager, Authorization $authorization): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::UTILISATEUR, 'utilisateur' => $this->getUser()->getId()]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        // Récupération des statistiques
        $globalStats = $this->statisticsService->getGlobalStatistics();
        $departementStats = $this->statisticsService->getStatisticsByDepartement();
        $creationsByMonth = $this->statisticsService->getCreationsByMonth(6);
        $recentConsultations = $this->statisticsService->getRecentConsultations(10);
        $upcomingDestructions = $this->statisticsService->getUpcomingDestructions();
        $demandesStats = $this->statisticsService->getDemandesAccessStatistics();
        $topActiveUsers = $this->statisticsService->getTopActiveUsers(5);
        $fileFormatDistribution = $this->statisticsService->getFileFormatDistribution();


        $adminDashboardResponse = $this->render('administrator/dashboard.html.twig', [
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
            'global_stats' => $globalStats,
            'departement_stats' => $departementStats,
            'creations_by_month' => $creationsByMonth,
            'recent_consultations' => $recentConsultations,
            'upcoming_destructions' => $upcomingDestructions,
            'demandes_stats' => $demandesStats,
            'top_active_users' => $topActiveUsers,
            'file_format_distribution' => $fileFormatDistribution,
        ]);

        $this->setAdminMercureCookie($request, $authorization);

        $adminDashboardResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]); 

        return $adminDashboardResponse;
    }

        private function setAdminMercureCookie(Request $request, Authorization $authorization)
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $baseUrl = $this->getParameter('app.base_url');
        $authorization->setCookie($request, [
            "{$baseUrl}/users/{$user->getId()}",
            "{$baseUrl}/status"
        ]);
    }
}
