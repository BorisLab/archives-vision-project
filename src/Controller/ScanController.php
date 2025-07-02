<?php

namespace App\Controller;

use Throwable;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\StatutMessage;
use App\Entity\StatutNotification;
use App\Repository\DossierRepository;
use App\Entity\NiveauAccesNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ScanController extends AbstractController
{

    public function __construct(private HttpClientInterface $client) {}

    #[Route('/scanner/devices', name: 'scanner_devices')]
    public function listDevices(): JsonResponse
    {
        try {
            $response = $this->client->request('GET', 'http://localhost:7777/devices');
            $data = $response->toArray();

            return new JsonResponse($data);
        } catch (Throwable $e) {
            return new JsonResponse([
                'error' => 'Impossible de contacter l’agent de scan local.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

#[Route('/scanner/scan-request', name: 'scanner_scan')]
public function scanRequest(Request $request): JsonResponse
{
    $content = json_decode($request->getContent(), true);
    $device = $content['device'] ?? null;

    if (!$device) {
        return $this->json(['success' => false, 'message' => 'Périphérique non fourni'], 400);
    }

    try {
        $response = $this->client->request('POST', 'http://localhost:7777/scan', [
            'json' => ['device' => $device],
        ]);

        $result = $response->toArray();

        return $this->json([
            'success' => true,
            'imageUrl' => $result['imageUrl']
        ]);
    } catch (\Exception $e) {
        return $this->json([
            'success' => false,
            'message' => 'Erreur lors de la communication avec l’agent local.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    #[Route('/archivist/scan', name: 'app_scan')]
    public function index(EntityManagerInterface $entityManager, HubInterface $hub, DossierRepository $dossierRepo): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ARCHIVISTE]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);
        $dossiers = $dossierRepo->findAll();

        return $this->render('archivemanager/scan.html.twig', [
            'scan_page' => 'ScanController',
            'dossiers' => $dossiers,
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
        ]);
    }
}
