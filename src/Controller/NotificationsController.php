<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Entity\Message;
use App\Entity\Utilisateur;
use App\Entity\DemandeAcces;
use App\Entity\Notification;
use App\Entity\StatutMessage;
use App\Entity\TypeNotification;
use App\Entity\StatutDemandeAcces;
use App\Entity\StatutNotification;
use App\Service\AuditLogger;
use App\Service\BordereauPretService;
use Symfony\Component\Mercure\Update;
use App\Entity\NiveauAccesNotification;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotificationsController extends AbstractController
{
    private AuditLogger $auditLogger;
    private BordereauPretService $bordereauPretService;

    public function __construct(AuditLogger $audit, BordereauPretService $bordereauPretService)
    {
        $this->auditLogger = $audit;
        $this->bordereauPretService = $bordereauPretService;
    }
    #[Route('/archivist/notifications', name: 'app_archivist_notifs')]
    #[IsGranted("ROLE_ARCHIVIST")]
    public function notifArchivist(Request $request, NotificationRepository $notificationRepository, EntityManagerInterface $entityManager, HubInterface $hub, Authorization $authorization): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ARCHIVISTE]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $this->setArchivistMercureCookie($request, $authorization);

        $reqNotifsArchivist = $notificationRepository->findBy(['type' => TypeNotification::DEMANDE, 'niveau_acces' => NiveauAccesNotification::ARCHIVISTE]);
        $msgNotifsArchivist = $notificationRepository->findBy(['type' => TypeNotification::MESSAGE, 'niveau_acces' => NiveauAccesNotification::ARCHIVISTE, 'utilisateur' => $this->getUser()]);

        if($request->isMethod('POST')){
            $action = $request->request->get('_action');

            switch($action) {
                //Dossiers
                case 'agree':
                    return $this->handleAgreeAccessRequest($request, $entityManager, $hub);
                case 'reject':
                    return $this->handleRejectAccessRequest($request, $entityManager, $hub);
                case 'markAsRead':
                    return $this->markChatNotifAsRead($request, $entityManager);
            }
        }  

        $listNotifsArchivist = $this->render('notifications/notifarchivist.html.twig', [
            'req_notifications_archivist' => $reqNotifsArchivist,
            'msg_notifications_archivist' => $msgNotifsArchivist,
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
        ]);

        $listNotifsArchivist->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);

        return $listNotifsArchivist;
    }

    #[Route('/user/notifications', name: 'app_user_notifs')]
    public function notifUser(Request $request, NotificationRepository $notificationRepository, EntityManagerInterface $entityManager, HubInterface $hub, Authorization $authorization): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::UTILISATEUR, 'utilisateur' => $this->getUser()->getId()]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $this->setUserMercureCookie($request, $authorization);

        if($request->isMethod('POST')){
            $id_notif = $request->request->get("notifId");
            $notif = $entityManager->getRepository(Notification::class)->find($id_notif);

            if(!$notif) {
                throw $this->createNotFoundException('La notification est introuvable.');
            } else $notif->setStatut(StatutNotification::LU);

            $entityManager->flush();

            return $this->redirectToRoute('app_user_notifs');
        }  

        $reqNotifsUser = $notificationRepository->findBy(['type' => [TypeNotification::INFO_REPONSE,TypeNotification::APPROB_REPONSE,TypeNotification::REJET_REPONSE,TypeNotification::REVOC_REPONSE,TypeNotification::MESSAGE], 'utilisateur' => $this->getUser()]);
        $msgNotifsUser = $notificationRepository->findBy(['type' => [TypeNotification::MESSAGE], 'niveau_acces' => NiveauAccesNotification::UTILISATEUR, 'utilisateur' => $this->getUser()]);


        $listNotifsUser = $this->render('notifications/notifuser.html.twig', [
            'req_notifications_user' => $reqNotifsUser,
            'msg_notifications_user' => $msgNotifsUser,
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
        ]);

        $listNotifsUser->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);

        return $listNotifsUser;
    }

    private function handleRejectAccessRequest(Request $request, EntityManagerInterface $entityManager, HubInterface $hub) {
        
        $id_notif = $request->request->get("notifId");
        $id_req = $request->request->get("reqId");
        $motif_rej = $request->request->get("rejectComInput");

        $notif = $entityManager->getRepository(Notification::class)->find($id_notif);
        $req = $entityManager->getRepository(DemandeAcces::class)->find($id_req);

        if(!$notif) {
            throw $this->createNotFoundException('La notification est introuvable.');
        } else $notif->setStatut(StatutNotification::LU);
        
        if(!$req) {
            throw $this->createNotFoundException('La demande d\'accès est introuvable.');
        } else { 
            if($req->getStatut() == StatutDemandeAcces::EN_ATTENTE){
            $req->setStatut(StatutDemandeAcces::REJETE);
            
            if($motif_rej){
              $req->setMotifRejet($motif_rej);
            }

            $req->setArchivisteId($this->getUser()->getId());

            $userToNotif = $notif->getUtilisateur();

            $update = new Update(
                $this->getParameter('app.base_url') . '/users/' . $userToNotif->getId(), 
                json_encode([
                    'type' => 'system',
                    'message' => "Votre dernière demande d'accès a été rejetée"
                ]),
                true
            );

            $hub->publish($update);

            $entityManager->flush();

            if($req->getDossier() !== NULL){
                $userMsgToSave = "Dossier : " . $req->getDossier()->getLibelleDossier() . ", Format : " . $req->getDossier()->getFormat() . ", Motif du rejet : " . $req->getMotifRejet();           
            }
            elseif($req->getFichier() !== NULL){
                $userMsgToSave = "Fichier : " . $req->getFichier()->getLibelleFichier() . ", Format : " . $req->getFichier()->getFormat() . ", Type : " . $req->getFichier()->getType() . ", Motif du rejet : " . $req->getMotifRejet();           
            }

            // Audit log - rejection
            $entityType = $req->getDossier() !== NULL ? 'Dossier' : 'Fichier';
            $entityId = $req->getDossier() !== NULL ? $req->getDossier()->getDossierId() : $req->getFichier()->getFichierId();
            $this->auditLogger->logReject('DemandeAcces', $req->getId(), [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'motif_rejet' => $motif_rej,
                'requester_id' => $req->getUtilisateur()->getId(),
            ]);

            $this->createDemandeAccesRejectNotificationForUser($userMsgToSave, $req->getUtilisateur(), $req, $entityManager);

            $this->addFlash('access_request_reject_success', 'Demande d\'accès rejetée avec succès');

            //$this->addFlash('folder_edit_error', 'Echec de la modification du dossier');
            return $this->redirectToRoute('app_archivist_notifs');
            } else {
                $this->addFlash('access_request_reject_error', 'Opération impossible à réaliser');
                return $this->redirectToRoute('app_archivist_notifs');
            }
        }
    }

    private function handleAgreeAccessRequest(Request $request, EntityManagerInterface $entityManager, HubInterface $hub) {
        
        $id_notif = $request->request->get("notifId");
        $id_req = $request->request->get("reqId");

        $notif = $entityManager->getRepository(Notification::class)->find($id_notif);
        $req = $entityManager->getRepository(DemandeAcces::class)->find($id_req);

        if(!$notif) {
            throw $this->createNotFoundException('La notification est introuvable.');
        } else $notif->setStatut(StatutNotification::LU);
        
        if(!$req) {
            throw $this->createNotFoundException('La demande d\'accès est introuvable.');
        } else {
            if($req->getStatut() == StatutDemandeAcces::EN_ATTENTE){ 
            $duration = $request->request->get("duration");

            $validDurations = [4320, 8760];
            if (!in_array($duration, $validDurations)) {
                $this->addFlash('access_request_agree_error', 'Durée d\'accès invalide.');
                return $this->redirectToRoute('app_archivist_notifs');
            }

            $req->setExpiration(new \DateTimeImmutable("+{$duration} hours"));
            $req->setStatut(StatutDemandeAcces::APPROUVE);

            $req->setArchivisteId($this->getUser()->getId());
            $req->setApprobateur($this->getUser());
            $req->setDateTraitement(new \DateTime());

            // Générer bordereau de prêt si document physique
            try {
                $bordereauPath = $this->bordereauPretService->generateBordereau($req);
                if ($bordereauPath) {
                    $req->setBordereauPret($bordereauPath);
                }
            } catch (\Exception $e) {
                // Log l'erreur mais ne bloque pas l'approbation
                $this->auditLogger->log('error', 'BordereauPret', $req->getId(), [
                    'error' => $e->getMessage()
                ]);
            }

            $userToNotif = $notif->getUtilisateur();

            $update = new Update(
                $this->getParameter('app.base_url') . '/users/' . $userToNotif->getId(), 
                json_encode([
                    'type' => 'system',
                    'message' => "Votre dernière demande d'accès a été accordée"
                ]),
                true
            );

            $hub->publish($update);

            if($req->getDossier() !== NULL){
                // Approuver les demandes pour les sous-dossiers et fichiers enfants
                $this->applyAgreeRequestToChildren($req->getDossier(), $duration, $req->getUtilisateur(), $entityManager);
                $userMsgToSave = "Dossier : " . $req->getDossier()->getLibelleDossier() . ", Format : " . $req->getDossier()->getFormat() . ", Durée d'accès : " . $duration . " heures";           
            }
            elseif($req->getFichier() !== NULL){
                $userMsgToSave = "Fichier : " . $req->getFichier()->getLibelleFichier() . ", Format : " . $req->getFichier()->getFormat() . ", Type : " . $req->getFichier()->getType() . ", Durée d'accès : " . $duration . " heures";           
            }

            // Génération conditionnelle du bordereau de prêt — uniquement pour les formats physiques
            $shouldGenerateBordereau = false;
            if ($req->getDossier() !== null && method_exists($req->getDossier(), 'getFormat') && $req->getDossier()->getFormat() === 'Physique') {
                $shouldGenerateBordereau = true;
            } elseif ($req->getFichier() !== null && method_exists($req->getFichier(), 'getFormat') && $req->getFichier()->getFormat() === 'Physique') {
                $shouldGenerateBordereau = true;
            }

            if ($shouldGenerateBordereau) {
                try {
                    $path = $this->bordereauPretService->generateBordereauPret($req);
                    $req->setBordereauPret($path);
                } catch (\Exception $e) {
                    // Ne bloque pas l'approbation — avertir l'archiviste
                    $this->addFlash('bordereau_error', 'Erreur lors de la génération du bordereau de prêt. Le PDF pourra être généré ultérieurement.');
                }
            }

            $entityManager->flush();

            // Audit log - approval
            $entityType = $req->getDossier() !== NULL ? 'Dossier' : 'Fichier';
            $entityId = $req->getDossier() !== NULL ? $req->getDossier()->getDossierId() : $req->getFichier()->getFichierId();
            $this->auditLogger->logApprove('DemandeAcces', $req->getId(), [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'duration_hours' => $duration,
                'requester_id' => $req->getUtilisateur()->getId(),
            ]);

            $this->createDemandeAccesAgreeNotificationForUser($userMsgToSave, $req->getUtilisateur(), $req, $entityManager);

            $this->addFlash('access_request_agree_success', 'Demande d\'accès accordée avec succès');

            //$this->addFlash('folder_edit_error', 'Echec de la modification du dossier');
            return $this->redirectToRoute('app_archivist_notifs');
           } else {
                   $this->addFlash('access_request_agree_error', 'Opération impossible à réaliser');
                   return $this->redirectToRoute('app_archivist_notifs');
           }
        }
    }

    private function markChatNotifAsRead(Request $request, EntityManagerInterface $entityManager)  {
            $id_notif = $request->request->get("notifId");
            $notif = $entityManager->getRepository(Notification::class)->find($id_notif);

            if(!$notif) {
                throw $this->createNotFoundException('La notification est introuvable.');
            } else $notif->setStatut(StatutNotification::LU);

            $entityManager->flush();

            return $this->redirectToRoute('app_archivist_notifs');
    }  

    private function applyAgreeRequestToChildren(Dossier $dossier, $duration, Utilisateur $utilisateur, EntityManagerInterface $entityManager)
    {
        foreach ($dossier->getDossiers() as $sousDossier) {
            $existingReq = $entityManager->getRepository(DemandeAcces::class)
                ->findOneBy([
                    'statut' => StatutDemandeAcces::EN_ATTENTE,
                    'dossier' => $sousDossier,
                    'utilisateur' => $utilisateur, 
                ]);

            if($existingReq) {
                $validDurations = [4320, 8760];
                if (!in_array($duration, $validDurations)) {
                    $this->addFlash('access_request_agree_error', 'Durée d\'accès invalide.');
                    return $this->redirectToRoute('app_archivist_notifs');
                }

                $existingReq->setStatut(StatutDemandeAcces::APPROUVE);
                $existingReq->setExpiration(new \DateTimeImmutable("+{$duration} hours"));
                $existingReq->setArchivisteId($this->getUser()->getId());

            }

            // Répéter pour les enfants des sous-dossiers
            $this->applyAgreeRequestToChildren($sousDossier, $duration, $utilisateur, $entityManager);
        }

        foreach ($dossier->getFichiers() as $fichier) {

            $existingReq = $entityManager->getRepository(DemandeAcces::class)
            ->findOneBy([
                'statut' => StatutDemandeAcces::EN_ATTENTE,
                'fichier' => $fichier,
                'utilisateur' => $utilisateur, 
            ]);

            if($existingReq) {
                $validDurations = [4320, 8760];
                if (!in_array($duration, $validDurations)) {
                    $this->addFlash('access_request_agree_error', 'Durée d\'accès invalide.');
                    return $this->redirectToRoute('app_archivist_notifs');
                }

                $existingReq->setStatut(StatutDemandeAcces::APPROUVE);
                $existingReq->setExpiration(new \DateTimeImmutable("+{$duration} hours"));
                $existingReq->setArchivisteId($this->getUser()->getId());
            }
        }
    }

    private function createDemandeAccesRejectNotificationForUser($message, $user, $notifReq, EntityManagerInterface $entityManager) {

        $notification = new Notification();
        $notification->setType(TypeNotification::REJET_REPONSE);
        $notification->setNiveauAcces(NiveauAccesNotification::UTILISATEUR);
        $notification->setStatut(StatutNotification::NON_LU);
        $notification->setMessage($message);
        $notification->setUtilisateur($user);
        $notification->setDemandeAcces($notifReq);

        $entityManager->persist($notification);
        $entityManager->flush();
    }

    private function createDemandeAccesAgreeNotificationForUser($message, $user, $notifReq, EntityManagerInterface $entityManager) {

        $notification = new Notification();
        $notification->setType(TypeNotification::APPROB_REPONSE);
        $notification->setNiveauAcces(NiveauAccesNotification::UTILISATEUR);
        $notification->setStatut(StatutNotification::NON_LU);
        $notification->setMessage($message);
        $notification->setUtilisateur($user);
        $notification->setDemandeAcces($notifReq);

        $entityManager->persist($notification);
        $entityManager->flush();
    }

    
    private function setUserMercureCookie(Request $request, Authorization $authorization)
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        $baseUrl = $this->getParameter('app.base_url');
        $authorization->setCookie($request, [
            "{$baseUrl}/users/{$user->getId()}" // Définit le topic auquel cet utilisateur peut s'abonner
        ]);
    }

    private function setArchivistMercureCookie(Request $request, Authorization $authorization)
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        $baseUrl = $this->getParameter('app.base_url');
        $authorization->setCookie($request, [
            "{$baseUrl}/archivists" // Définit le topic auquel cet utilisateur peut s'abonner
        ]);
    }
}
