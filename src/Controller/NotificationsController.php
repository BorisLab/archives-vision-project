<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationsController extends AbstractController
{
    #[Route('/archivist/notifications', name: 'app_archivist_notifs')]
    public function notifarchivist(): Response
    {
        return $this->render('notifications/notifarchivist.html.twig', [
            'controller_name' => 'ArchivistNotifPage',
        ]);
    }

    #[Route('/user/notifications', name: 'app_user_notifs')]
    public function notifuser(): Response
    {
        return $this->render('notifications/notifuser.html.twig', [
            'controller_name' => 'UserNotifPage',
        ]);
    }
}
