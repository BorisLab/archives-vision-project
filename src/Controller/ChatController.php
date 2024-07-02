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
        $userChatResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]); 
        return $userChatResponse;
    }

    #[Route('/archivist/chat', name: 'app_archivist_chat')]
    public function archchat(): Response
    {
        $archivistChatResponse = $this->render('archivemanager/chat-archman.html.twig', [
            'archivist_chat' => 'ArchivistChatPage',
        ]);
        $archivistChatResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);   
        return $archivistChatResponse;
    }
}
