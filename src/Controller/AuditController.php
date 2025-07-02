<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Departement;
use App\Entity\Utilisateur;
use App\Entity\Notification;
use App\Entity\StatutMessage;
use App\Entity\TypeNotification;
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
}
