<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Entity\Message;
use App\Entity\Departement;
use App\Entity\Utilisateur;
use App\Entity\DemandeAcces;
use App\Entity\Notification;
use App\Entity\StatutMessage;
use App\Entity\TypeNotification;
use App\Entity\StatutDemandeAcces;
use App\Entity\StatutNotification;
use App\Repository\DossierRepository;
use Symfony\Component\Mercure\Update;
use App\Entity\NiveauAccesNotification;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DemandeAccesRepository;
use App\Service\BordereauPretService;
use App\Service\AuditLogger;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;

class AccueilController extends AbstractController
{
    private PaginatorInterface $paginator;
    private BordereauPretService $bordereauPretService;
    private AuditLogger $auditLogger;

    public function __construct(
        PaginatorInterface $paginator,
        BordereauPretService $bordereauPretService,
        AuditLogger $auditLogger
    ) {
        $this->paginator = $paginator;
        $this->bordereauPretService = $bordereauPretService;
        $this->auditLogger = $auditLogger;
    }

    #[Route(['/user/home'], name: 'app_user_home')]
    #[IsGranted("ROLE_USER")]
    public function userhome(Request $request, DossierRepository $dossierRepository, DemandeAccesRepository $demandeAccesRepository, EntityManagerInterface $entityManager, Authorization $authorization): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::UTILISATEUR, 'utilisateur' => $this->getUser()->getId()]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutNotification::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $user = $this->getUser();

        $userDep = $user->getDepartement();
        
        // Build query for fonds with pagination
        if($user->isDG()){
            $queryBuilder = $dossierRepository->createQueryBuilder('d')
                ->where('d.parent = :parent')
                ->setParameter('parent', false)
                ->orderBy('d.date_creation', 'DESC');
        }
        else{
            $queryBuilder = $dossierRepository->createQueryBuilder('d')
                ->where('d.parent = :parent')
                ->andWhere('d.departement = :departement')
                ->setParameter('parent', false)
                ->setParameter('departement', $userDep)
                ->orderBy('d.date_creation', 'DESC');
        }

        $fonds = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20 // Items per page
        );

        $dossiers = $entityManager->getRepository(Dossier::class)->findAll();

        foreach ($dossiers as $dossier) {
            $dossier->hasPendingRequest = $demandeAccesRepository->findOneBy([
               'dossier' => $dossier,
               'statut' => 'pending',
               'utilisateur' => $this->getUser(),
            ]) !== null;
        }

        foreach ($dossiers as $dossier) {
            $dossier->hasApprovedRequest = $demandeAccesRepository->findOneBy([
               'dossier' => $dossier,
               'statut' => 'approved',
               'utilisateur' => $this->getUser(),
            ], ['expiration' => 'DESC']); // Trie par date d'expiration décroissante

            if ($dossier->hasApprovedRequest) {
                // Vérifie que la date d'expiration est encore valide
                $expiration = $dossier->hasApprovedRequest->getExpiration();
                $dossier->hasApprovedRequest = $expiration > new \DateTimeImmutable(); // true si la date n'est pas expirée
            }


            $demandeAccesDos = $demandeAccesRepository->findOneBy([
                'dossier' => $dossier,
                'statut' => 'approved',
                'utilisateur' => $this->getUser(),
            ], ['expiration' => 'DESC']);
    
            if ($demandeAccesDos) {
                $nowDos = new \DateTimeImmutable();
                $expirationDos = $demandeAccesDos->getExpiration();
    
                if ($expirationDos > $nowDos) {
                    // Temps restant en secondes
                    $dossier->remainingAccessTimeInSeconds = $expirationDos->getTimestamp() - $nowDos->getTimestamp();
                } else {
                    $dossier->remainingAccessTimeInSeconds = null;
                }
            } else {
                $dossier->remainingAccessTimeInSeconds = null;
            }
        }

        $userHomeResponse = $this->render('user/fonds.html.twig', [
            'dossiers' => $fonds,
            'fichiers' => [],
            'dossier_courant' => null,
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
        ]);

        $this->setUserMercureCookie($request, $authorization);

        $userHomeResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);   

        return $userHomeResponse;
    }

    #[Route('/user/{id<[0-9]+>}/répertoire', name: 'app_user_folder_content')]
    #[IsGranted("ROLE_USER")]
    public function sousFondsContentForUser(Request $request, EntityManagerInterface $entityManager, DemandeAccesRepository $demandeAccesRepository, int $id, Authorization $authorization) : Response
    {
        $this->setUserMercureCookie($request, $authorization);
        
        $deps = $entityManager->getRepository(Departement::class)->findAll();

        //Gestion des sous-dossiers
        $dossierCourant = $entityManager->getRepository(Dossier::class)->find($id);
        $dossierParent = $dossierCourant->getDossierParent();
        $dossiers = $dossierCourant ? $dossierCourant->getDossiers()->toArray() : [];
        $arborescence = $dossierParent ? $dossierParent->getArborescence() : [];

        $dossierRacine = $dossierCourant->getDossierRacine();

        //Gestions des fichiers
        $fichiers = $dossierCourant ? $dossierCourant->getFichiers()->toArray() : [];

        $formats = '[{"name": "--Choisir--", "value": ""}, {"name": "Physique", "value": "Physique"}, {"name": "Numérique", "value": "Numérique"}, {"name": "Mixte", "value": "Mixte"}]';
        $statuts = '[{"name": "Disponible", "value": 1}, {"name": "Indisponible", "value": null}]';

        foreach ($dossiers as $dossier) {
            $dossier->hasPendingRequest = $demandeAccesRepository->findOneBy([
                'dossier' => $dossier,
                'statut' => 'pending',
                'utilisateur' => $this->getUser(),
            ]) !== null;
        }

        foreach ($dossiers as $dossier) {
            $dossier->hasApprovedRequest = $demandeAccesRepository->findOneBy([
                'dossier' => $dossier,
                'statut' => 'approved',
                'utilisateur' => $this->getUser(),
            ], ['expiration' => 'DESC']); // Trie par date d'expiration décroissante

            if ($dossier->hasApprovedRequest) {
                // Vérifie que la date d'expiration est encore valide
                $expiration = $dossier->hasApprovedRequest->getExpiration();
                $dossier->hasApprovedRequest = $expiration > new \DateTimeImmutable(); // true si la date n'est pas expirée
            }

            $demandeAccesDos = $demandeAccesRepository->findOneBy([
                'dossier' => $dossier,
                'statut' => 'approved',
                'utilisateur' => $this->getUser(),
            ], ['expiration' => 'DESC']);
    
            if ($demandeAccesDos) {
                $nowDos = new \DateTimeImmutable();
                $expirationDos = $demandeAccesDos->getExpiration();
    
                if ($expirationDos > $nowDos) {
                    // Temps restant en secondes
                    $dossier->remainingAccessTimeInSeconds = $expirationDos->getTimestamp() - $nowDos->getTimestamp();
                } else {
                    $dossier->remainingAccessTimeInSeconds = null;
                }
            } else {
                $dossier->remainingAccessTimeInSeconds = null;
            }
        }

        foreach ($fichiers as $fichier) {
            $fichier->hasPendingRequest = $demandeAccesRepository->findOneBy([
                'fichier' => $fichier,
                'statut' => 'pending',
                'utilisateur' => $this->getUser(),
            ]) !== null;
        }

        foreach ($fichiers as $fichier) {
            $fichier->hasApprovedRequest = $demandeAccesRepository->findOneBy([
                'fichier' => $fichier,
                'statut' => 'approved',
                'utilisateur' => $this->getUser(),
            ], ['expiration' => 'DESC']); // Trie par date d'expiration décroissante

            if ($fichier->hasApprovedRequest) {
                // Vérifie que la date d'expiration est encore valide
                $expiration = $fichier->hasApprovedRequest->getExpiration();
                $fichier->hasApprovedRequest = $expiration > new \DateTimeImmutable(); // true si la date n'est pas expirée
            }

            $demandeAccesFic = $demandeAccesRepository->findOneBy([
                'fichier' => $fichier,
                'statut' => 'approved',
                'utilisateur' => $this->getUser(),
            ], ['expiration' => 'DESC']);
    
            if ($demandeAccesFic) {
                $nowFic = new \DateTimeImmutable();
                $expirationFic = $demandeAccesFic->getExpiration();
    
                if ($expirationFic > $nowFic) {
                    // Temps restant en secondes
                    $fichier->remainingAccessTimeInSeconds = $expirationFic->getTimestamp() - $nowFic->getTimestamp();
                } else {
                    $fichier->remainingAccessTimeInSeconds = null;
                }
            } else {
                $fichier->remainingAccessTimeInSeconds = null;
            }
        }

        usort($dossiers, function ($a, $b) {
            return $b->getDateCreation() <=> $a->getDateCreation();
        });

        usort($fichiers, function ($a, $b) {
            return $b->getDateCreation() <=> $a->getDateCreation();
        });

        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::UTILISATEUR]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutNotification::NON_LU, 'recipient' => $this->getUser()->getId()]);


        $fichiersAndFolders = $this->render('user/dossier-contenu.html.twig', [
            'dossiers' => $dossiers,
            'fichiers' => $fichiers,
            'formats' => json_decode($formats, true),
            'statuts' => json_decode($statuts, true),
            'deps' => $deps,
            'dossier_courant' => $dossierCourant,
            'dossier_racine' => $dossierRacine,
            'arborescence' => $arborescence,
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
        ]);

        $fichiersAndFolders->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);

        return $fichiersAndFolders;
    }

    #[Route(['/archivist/home'], name: 'app_archivist_home')]
    #[IsGranted("ROLE_ARCHIVIST")]
    public function archivisthome(Request $request, EntityManagerInterface $entityManager, HubInterface $hub, Authorization $authorization): Response
    {

        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ARCHIVISTE]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);


        if($request->isMethod('POST')){
            $action = $request->request->get('_action');

            switch($action) {
                //Dossiers
                case 'agree':
                    return $this->handleAgreeAccessRequest($request, $entityManager, $hub);
                case 'reject':
                    return $this->handleRejectAccessRequest($request, $entityManager, $hub);
                case 'revoke':
                    return $this->handleRevokeAccessRequest($request, $entityManager, $hub);
            }
        } 

        $demandesAcces = $entityManager->getRepository(DemandeAcces::class)->findAll();

        // Trier du plus récent au plus ancien
        usort($demandesAcces, function ($a, $b) {
            return $b->getDateCreation() <=> $a->getDateCreation();
        });
        
        // Initialisation des tableaux pour filtrer les doublons
        $filteredDemandes = [];
        $seenParents = [];
        
        /**
         * Fonction récursive pour récupérer tous les sous-dossiers d'un dossier donné
         */
        function getAllSubDossiers($dossier) {
            $subDossiers = [];
            foreach ($dossier->getDossiers() as $subDossier) {
                $subDossiers[] = $subDossier->getDossierId();
                $subDossiers = array_merge($subDossiers, getAllSubDossiers($subDossier));
            }
            return $subDossiers;
        }
        
        foreach ($demandesAcces as $demande) {
            $parent = $demande->getDossier() ?: ($demande->getFichier() ? $demande->getFichier()->getDossier() : null);
            $createdAt = $demande->getDateCreation()->format('Y-m-d H:i:s');
        
            if ($parent) {
                // Vérifier si ce dossier ou un de ses sous-dossiers a déjà été traité
                if (isset($seenParents[$parent->getDossierId()]) && $seenParents[$parent->getDossierId()] === $createdAt) {
                    continue;
                }
        
                // Ajouter aussi tous ses sous-dossiers pour éviter les doublons
                foreach (getAllSubDossiers($parent) as $subDossierId) {
                    $seenParents[$subDossierId] = $createdAt;
                }
        
                // Marquer ce dossier comme déjà vu
                $seenParents[$parent->getDossierId()] = $createdAt;
            }
        
            // Ajouter la demande filtrée
            $filteredDemandes[] = $demande;
        }

        // Paginate filtered results
        $pagination = $this->paginator->paginate(
            $filteredDemandes,
            $request->query->getInt('page', 1),
            15 // Items per page
        );

        $archivistHomeResponse = $this->render('archivemanager/index.html.twig', [
            'archivist_home' => 'ArchivistHomePage',
            'demandes' => $pagination,
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread
        ]);

        $this->setArchivistMercureCookie($request, $authorization); 

        $archivistHomeResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);   

        return $archivistHomeResponse;
    }

    #[Route(['/admin/home'], name: 'app_admin_home')]
    #[IsGranted("ROLE_ADMIN")]
    public function adminhome(EntityManagerInterface $entityManager, Request $request, Authorization $authorization): Response
    {
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);
        $nbrDeps = $entityManager->getRepository(Departement::class)->count([]);
        $nbrUsers = $entityManager->getRepository(Utilisateur::class)->count([]);

        $adminHomeResponse = $this->render('administrator/index.html.twig', [
            'admin_home' => 'AdminHomePage',
            'nbr_msgs_unread' => $nbrMsgsUnread,
            'nbr_deps' => $nbrDeps,
            'nbr_users' => $nbrUsers
        ]);

        $this->setAdminMercureCookie($request, $authorization); 

        $adminHomeResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]); 

        return $adminHomeResponse;
    }

    public function sendNbreNotifications(HubInterface $hub, string $url, int $nbreNotifs)
    {
        $update = new Update(
            $url,
            json_encode(['unreadCount' => $nbreNotifs])
        );

        $hub->publish($update);
    }

    private function handleRejectAccessRequest(Request $request, EntityManagerInterface $entityManager, HubInterface $hub) {
        
        $id_req = $request->request->get("reqId");
        $motif_rej = $request->request->get("rejectComInput");

        $req = $entityManager->getRepository(DemandeAcces::class)->find($id_req);
        
        if(!$req) {
            throw $this->createNotFoundException('La demande d\'accès est introuvable.');
        } else { 
            if($req->getStatut() == StatutDemandeAcces::EN_ATTENTE){
            $req->setStatut(StatutDemandeAcces::REJETE);
            
            if($motif_rej){
              $req->setMotifRejet($motif_rej);
            }

            $notifToRead = $entityManager->getRepository(Notification::class)->findOneBy(['demande_acces' => $req->getId(), 'type' => TypeNotification::DEMANDE]);
            $notifToRead->setStatut(StatutNotification::LU);

            $req->setArchivisteId($this->getUser()->getId());

            $userToNotif = $req->getUtilisateur();

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

            $this->createDemandeAccesRejectNotificationForUser($userMsgToSave, $req->getUtilisateur(), $req, $entityManager);

            $this->addFlash('access_request_reject_success', 'Demande d\'accès rejetée avec succès');

            //$this->addFlash('folder_edit_error', 'Echec de la modification du dossier');
            return $this->redirectToRoute('app_archivist_home');
            } else {
                $this->addFlash('access_request_reject_error', 'Opération impossible à réaliser');
                return $this->redirectToRoute('app_archivist_home');
            }
        }
    }

    private function handleAgreeAccessRequest(Request $request, EntityManagerInterface $entityManager, HubInterface $hub) {
        
        $id_req = $request->request->get("reqId");

        $req = $entityManager->getRepository(DemandeAcces::class)->find($id_req);

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

            $notifToRead = $entityManager->getRepository(Notification::class)->findOneBy(['demande_acces' => $req->getId(), 'type' => TypeNotification::DEMANDE]);
            $notifToRead->setStatut(StatutNotification::LU);


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

            $userToNotif = $req->getUtilisateur();

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
            
            $entityManager->flush();

            $this->createDemandeAccesAgreeNotificationForUser($userMsgToSave, $req->getUtilisateur(), $req, $entityManager);

            $this->addFlash('access_request_agree_success', 'Demande d\'accès accordée avec succès');

            //$this->addFlash('folder_edit_error', 'Echec de la modification du dossier');
            return $this->redirectToRoute('app_archivist_home');
            } else {
                $this->addFlash('access_request_agree_error', 'Opération impossible à réaliser');
                return $this->redirectToRoute('app_archivist_home');
            }
        }
    }

    private function handleRevokeAccessRequest(Request $request, EntityManagerInterface $entityManager, HubInterface $hub) {
        
        $id_req = $request->request->get("reqId");

        $req = $entityManager->getRepository(DemandeAcces::class)->find($id_req);
        
        if(!$req) {
            throw $this->createNotFoundException('La demande d\'accès est introuvable.');
        } else { 
            if($req->getStatut() == StatutDemandeAcces::APPROUVE){
            $req->setStatut(StatutDemandeAcces::REVOQUE);

            $req->setArchivisteId($this->getUser()->getId());

            $userToNotif = $req->getUtilisateur();

            $update = new Update(
                $this->getParameter('app.base_url') . '/users/' . $userToNotif->getId(), 
                json_encode([
                    'type' => 'system',
                    'message' => "Une de vos demandes d'accès approuvée a été révoquée"
                ]),
                true
            );

            $hub->publish($update);

            $entityManager->flush();

            if($req->getDossier() !== NULL){
                $userMsgToSave = "Dossier : " . $req->getDossier()->getLibelleDossier() . ", Format : " . $req->getDossier()->getFormat() . ", Accès révoqué ";           
            }
            elseif($req->getFichier() !== NULL){
                $userMsgToSave = "Fichier : " . $req->getFichier()->getLibelleFichier() . ", Format : " . $req->getFichier()->getFormat() . ", Accès révoqué ";           
            }

            $this->createDemandeAccesRevokedNotificationForUser($userMsgToSave, $req->getUtilisateur(), $req, $entityManager);

            $this->addFlash('access_revoke_success', 'Demande d\'accès révoquée avec succès');

            //$this->addFlash('folder_edit_error', 'Echec de la modification du dossier');
            return $this->redirectToRoute('app_archivist_home');
            } else {
                $this->addFlash('access_revoke_error', 'Opération impossible à réaliser');
                return $this->redirectToRoute('app_archivist_home');
            }
        }
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

    private function createDemandeAccesRevokedNotificationForUser($message, $user, $notifReq, EntityManagerInterface $entityManager) {

        $notification = new Notification();
        $notification->setType(TypeNotification::REVOC_REPONSE);
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
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $baseUrl = $this->getParameter('app.base_url');
        $authorization->setCookie($request, [
            "{$baseUrl}/users/{$user->getId()}",
            "{$baseUrl}/status"
        ]);
    }

    private function setArchivistMercureCookie(Request $request, Authorization $authorization)
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $baseUrl = $this->getParameter('app.base_url');
        $authorization->setCookie($request, [
            "{$baseUrl}/archivists",
            "{$baseUrl}/users/{$user->getId()}",
            "{$baseUrl}/status"
        ]);
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

    #[Route('/demande-acces/{id}/bordereau', name: 'app_demande_acces_bordereau')]
    #[IsGranted("ROLE_USER")]
    public function telechargerBordereau(DemandeAcces $demandeAcces, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier que l'utilisateur a le droit de télécharger (soit l'emprunteur, soit un archiviste)
        if ($demandeAcces->getUtilisateur() !== $user && !in_array('ROLE_ARCHIVIST', $user->getRoles()) && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce bordereau');
        }

        // Vérifier que la demande est approuvée
        if ($demandeAcces->getStatut() !== StatutDemandeAcces::APPROUVE) {
            $this->addFlash('error', 'La demande d\'accès doit être approuvée pour télécharger le bordereau');
            return $this->redirectToRoute('app_user_home');
        }

        // Vérifier qu'un bordereau existe
        $bordereauPath = $demandeAcces->getBordereauPret();
        if (!$bordereauPath) {
            $this->addFlash('error', 'Aucun bordereau disponible pour cette demande');
            return $this->redirectToRoute('app_user_home');
        }

        // Construire le chemin complet
        $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $bordereauPath;

        // Vérifier que le fichier existe
        if (!file_exists($fullPath)) {
            $this->addFlash('error', 'Le bordereau est introuvable');
            return $this->redirectToRoute('app_user_home');
        }

        // Log de téléchargement
        $this->auditLogger->log('download', 'BordereauPret', $demandeAcces->getId(), [
            'utilisateur' => $user->getEmail(),
            'fichier' => $bordereauPath
        ]);

        // Retourner le fichier PDF
        return $this->file($fullPath, 'bordereau_pret_' . $demandeAcces->getId() . '.pdf');
    }
}
