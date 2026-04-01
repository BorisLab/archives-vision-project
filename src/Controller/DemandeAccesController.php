<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Entity\Fichier;
use App\Entity\Message;
use App\Form\DossierType;
use App\Entity\Departement;
use App\Entity\Utilisateur;
use App\Entity\DemandeAcces;
use App\Entity\Notification;
use App\Entity\TypeNotification;
use App\Entity\StatutDemandeAcces;
use App\Entity\StatutNotification;
use Symfony\Component\Mercure\Update;
use App\Entity\NiveauAccesNotification;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DemandeAccesRepository;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DemandeAccesController extends AbstractController
{
    #[Route('/folder/request-access', name: 'app_request_folder_access')]
    #[IsGranted("ROLE_USER")]
    public function requestAccessFolder(Request $request, EntityManagerInterface $entityManager, DemandeAccesRepository $demandeAccesRepository, HubInterface $hub): JsonResponse
    {

        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ARCHIVISTE]);

        if($request->isMethod('POST') && ($request->request->get('_action') === 'reqDossier')) {
            $id = $request->request->get('reqDossierId');
            $dossier = $entityManager->getRepository(Dossier::class)->find($id);

            if (!$dossier) {
               throw $this->createNotFoundException('Dossier introuvable.');
            }

            $existingReq = $entityManager->getRepository(DemandeAcces::class)
                ->findOneBy([
                    'statut' => StatutDemandeAcces::EN_ATTENTE,
                    'dossier' => $dossier,
                    'utilisateur' => $this->getUser(), 
                ]);

            if(!$existingReq) {
                $req = new DemandeAcces();
                $req->setUtilisateur($this->getUser());
                $req->setDossier($dossier);
                $req->setStatut(StatutDemandeAcces::EN_ATTENTE);

                $entityManager->persist($req);
            }

            // Ajouter les demandes pour les sous-dossiers et fichiers enfants
            $this->applyRequestToChildren($dossier, $req, $entityManager);

            $entityManager->flush();

            $archivistMsgToShow = sprintf("Dossier : %s\n Format du dossier : %s\n Département du demandeur : %s\n Nom du demandeur : %s",
                    $dossier->getLibelleDossier(),
                    $dossier->getFormat(),
                    $this->getUser()->getDepartement()->getLibelleDep(),
                    $this->getUser()->getNomComplet(),
            );

            $archivistMsgToSave = "Dossier : " . $dossier->getLibelleDossier() . ", Format : " . $dossier->getFormat() . ", Département du demandeur : " . $this->getUser()->getDepartement()->getLibelleDep() . ", Nom du demandeur : " . $this->getUser()->getNomComplet();           
            $userMsgToSave = "Votre demande d'accès à " . $dossier->getLibelleDossier() . " a été transmise avec succès.";
            
            // Publier une notification
            $update = new Update(
                $this->getParameter('app.base_url') . '/archivists', 
                json_encode([
                    'message' => nl2br($archivistMsgToShow),
                    'unread_notifs_count' => $nbrNotifsUnread,
                ]),
                true
            );

            $this->createDemandeAccesNotificationForArchivist($archivistMsgToSave, $req, $entityManager);

            $this->createDemandeAccesNotificationForUser($userMsgToSave, $req, $entityManager);

            $this->addFlash('add_request_success', 'Demande d\'accès envoyée avec succès');
            
            $hub->publish($update);
            
        }

        $referer = $request->headers->get('referer');

        $urlToRedirect = $referer;
        
        return new JsonResponse([
            'success' => true,
            'redirectUrl' => $urlToRedirect
        ]);
    }

    #[Route('/file/request-access/{id<[0-9]+>}', name: 'app_request_file_access')]
    #[IsGranted("ROLE_USER")]
    public function requestAccessFile(Request $request, EntityManagerInterface $entityManager, HubInterface $hub): JsonResponse
    {

        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ARCHIVISTE]);

        if($request->isMethod('POST') && ($request->request->get('_action') === 'reqFichier')) {
            $id = $request->request->get('reqFichierId');
            $fichier = $entityManager->getRepository(Fichier::class)->find($id);

            $req = new DemandeAcces();
            $req->setUtilisateur($this->getUser());
            $req->setFichier($fichier);
            $req->setStatut(StatutDemandeAcces::EN_ATTENTE);

            $entityManager->persist($req);
            $entityManager->flush();

            $archivistMsgToShow = sprintf("Fichier : %s\n Format du fichier : %s\n Type de fichier : %s\n Département du demandeur : %s\n Nom du demandeur : %s",
                    $fichier->getLibelleFichier(),
                    $fichier->getFormat(),
                    $fichier->getType(),
                    $this->getUser()->getDepartement()->getLibelleDep(),
                    $this->getUser()->getNomComplet(),
            );

            $archivistMsgToSave = "Fichier : " . $fichier->getLibelleFichier() . ", Format : " . $fichier->getFormat() . ", Type : " . $fichier->getType() . ", Département du demandeur : " . $this->getUser()->getDepartement()->getLibelleDep() . ", Nom du demandeur : " . $this->getUser()->getNomComplet();
            $userMsgToSave = "Votre demande d'accès à " . $fichier->getLibelleFichier() . " a été transmise avec succès.";
            
            // Publier une notification
            $update = new Update(
                $this->getParameter('app.base_url') . '/archivists', 
                json_encode([
                    'message' => nl2br($archivistMsgToShow),
                    'unread_notifs_count' => $nbrNotifsUnread,
                ]),
                true
            );

            $this->createDemandeAccesNotificationForArchivist($archivistMsgToSave, $req, $entityManager);

            $this->createDemandeAccesNotificationForUser($userMsgToSave, $req, $entityManager);

            $this->addFlash('add_request_success', 'Demande d\'accès envoyée avec succès');

            $hub->publish($update);
        }
                
        $referer = $request->headers->get('referer');

        $urlToRedirect = $referer;
        
        return new JsonResponse([
            'success' => true,
            'redirectUrl' => $urlToRedirect
        ]);
    }


    private function applyRequestToChildren(Dossier $dossier, DemandeAcces $parentRequest, EntityManagerInterface $entityManager)
    {
        foreach ($dossier->getDossiers() as $sousDossier) {

            $existingReq = $entityManager->getRepository(DemandeAcces::class)
                ->findOneBy([
                    'statut' => StatutDemandeAcces::EN_ATTENTE,
                    'dossier' => $sousDossier,
                    'utilisateur' => $this->getUser(), 
                ]);

            if(!$existingReq) {
                $req = new DemandeAcces();
                $req->setUtilisateur($parentRequest->getUtilisateur());
                $req->setDossier($sousDossier);
                $req->setStatut(StatutDemandeAcces::EN_ATTENTE);

                $entityManager->persist($req);
            }

            // Répéter pour les enfants des sous-dossiers
            $this->applyRequestToChildren($sousDossier, $parentRequest, $entityManager);
        }

        foreach ($dossier->getFichiers() as $fichier) {

            $existingReq = $entityManager->getRepository(DemandeAcces::class)
            ->findOneBy([
                'statut' => StatutDemandeAcces::EN_ATTENTE,
                'fichier' => $fichier,
                'utilisateur' => $this->getUser(), 
            ]);

            if(!$existingReq) {
                $req = new DemandeAcces();
                $req->setUtilisateur($parentRequest->getUtilisateur());
                $req->setFichier($fichier);
                $req->setStatut(StatutDemandeAcces::EN_ATTENTE);

                $entityManager->persist($req);
            }
        }
    }

    private function createDemandeAccesNotificationForArchivist($message, $notifReq, EntityManagerInterface $entityManager) {

        $notification = new Notification();
        $notification->setType(TypeNotification::DEMANDE);
        $notification->setNiveauAcces(NiveauAccesNotification::ARCHIVISTE);
        $notification->setStatut(StatutNotification::NON_LU);
        $notification->setMessage($message);
        $notification->setUtilisateur($this->getUser());
        $notification->setDemandeAcces($notifReq);

        $entityManager->persist($notification);
        $entityManager->flush();
    }

    private function createDemandeAccesNotificationForUser($message, $notifReq, EntityManagerInterface $entityManager) {

        $notification = new Notification();
        $notification->setType(TypeNotification::INFO_REPONSE);
        $notification->setNiveauAcces(NiveauAccesNotification::UTILISATEUR);
        $notification->setStatut(StatutNotification::NON_LU);
        $notification->setMessage($message);
        $notification->setUtilisateur($this->getUser());
        $notification->setDemandeAcces($notifReq);

        $entityManager->persist($notification);
        $entityManager->flush();
    }
}
