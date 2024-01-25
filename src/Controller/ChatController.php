<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ChatController extends AbstractController
{
    #[Route('/user/chat', name: 'app_user_chat')]
    public function userchat(): Response
    {
        $userChatResponse = $this->render('user/chat-user.html.twig', [
            'user_chat' => 'UserChatPage',
        ]);
        $userChatResponse->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');    
        $userChatResponse->headers->set('Pragma', 'no-cache');    
        $userChatResponse->headers->set('Expires', '0');  
        return $userChatResponse;
    }

    #[Route('/archivist/chat', name: 'app_archivist_chat')]
    public function archchat(): Response
    {
        $archivistChatResponse = $this->render('archivemanager/chat-archman.html.twig', [
            'archivist_chat' => 'ArchivistChatPage',
        ]);
        $archivistChatResponse->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');    
        $archivistChatResponse->headers->set('Pragma', 'no-cache');    
        $archivistChatResponse->headers->set('Expires', '0');  
        return $archivistChatResponse;
    }
}
