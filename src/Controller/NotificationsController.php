<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationsController extends AbstractController
{
    #[Route('/archivist/notifications', name: 'app_archivist_notifs')]
    public function index(): Response
    {
        return $this->render('notifications/index.html.twig', [
            'controller_name' => 'NotificationsController',
        ]);
    }
}
