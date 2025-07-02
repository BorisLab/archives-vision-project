<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Entity\Message;
use App\Form\DossierType;
use App\Entity\Departement;
use App\Entity\Utilisateur;
use App\Entity\Notification;
use App\Entity\StatutMessage;
use App\Entity\StatutUtilisateur;
use App\Entity\StatutNotification;
use App\Entity\NiveauAccesNotification;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UsersController extends AbstractController
{
    #[Route('/admin/users', name: 'app_admin_users')]
    #[IsGranted("ROLE_ADMIN")]
    public function userslist(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager) {

        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ADMINISTRATEUR]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $utilisateurs = $entityManager->getRepository(Utilisateur::class)->findAllExcept($this->getUser());

        $departements = $entityManager->getRepository(Departement::class)->findAll();

        if($request->isMethod('POST')){
            $action = $request->request->get('_action');

            switch($action) {
                //Dossiers
                case 'active':
                    return $this->handleActiveUserAccount($request, $entityManager);
                case 'disable':
                    return $this->handleDisableUserAccount($request, $entityManager);
            }
        }  

        $adminUsers = $this->render('administrator/users-page.html.twig', [
            'admin_users_home' => 'AdminUsersPage',
            'utilisateurs' => $utilisateurs,
            'deps' => $departements,
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread
        ]);

        $adminUsers->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);

        return $adminUsers;
    }

    private function handleActiveUserAccount(Request $request, EntityManagerInterface $entityManager) {
        
        $id_user = $request->request->get("aaccountId");

        $userToActive = $entityManager->getRepository(Utilisateur::class)->find($id_user);
        
        if(!$userToActive) {
            throw $this->createNotFoundException('Le compte utilisateur est introuvable.');
        } else {
            if($userToActive->getStatut() == StatutUtilisateur::INACTIF){ 

            $userToActive->setStatut(StatutUtilisateur::ACTIF);
            
            $entityManager->flush();

            $this->addFlash('user_account_active_success', 'Le compte utilisateur a été activé avec succès');

            //$this->addFlash('folder_edit_error', 'Echec de la modification du dossier');
            return $this->redirectToRoute('app_admin_users');
           } else {
                   $this->addFlash('user_account_active_error', 'Opération impossible à réaliser');
                   return $this->redirectToRoute('app_admin_users');
           }
        }
    }

    private function handleDisableUserAccount(Request $request, EntityManagerInterface $entityManager) {
        
        $id_user = $request->request->get("daccountId");

        $userToActive = $entityManager->getRepository(Utilisateur::class)->find($id_user);
        
        if(!$userToDisable) {
            throw $this->createNotFoundException('Le compte utilisateur est introuvable.');
        } else {
            if($userToDisable->getStatut() == StatutUtilisateur::ACTIF){ 

            $userToActive->setStatut(StatutUtilisateur::INACTIF);
            
            $entityManager->flush();

            $this->addFlash('user_account_disable_success', 'Le compte utilisateur a été désactivé avec succès');

            //$this->addFlash('folder_edit_error', 'Echec de la modification du dossier');
            return $this->redirectToRoute('app_admin_users');
           } else {
                   $this->addFlash('user_account_disable_error', 'Opération impossible à réaliser');
                   return $this->redirectToRoute('app_admin_users');
           }
        }
    }
}
