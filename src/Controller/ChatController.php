<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Utilisateur;
use App\Entity\Notification;
use App\Entity\StatutMessage;
use App\Entity\TypeNotification;
use App\Entity\StatutNotification;
use App\Repository\MessageRepository;
use Symfony\Component\Mercure\Update;
use App\Entity\NiveauAccesNotification;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatController extends AbstractController
{
    #[Route('/user/chat', name: 'app_user_chat')]
    #[IsGranted("ROLE_USER")]
    public function userchat(EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::UTILISATEUR, 'utilisateur' => $this->getUser()->getId()]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $archivists = $entityManager->getRepository(Utilisateur::class)->findByRoles("ROLE_ARCHIVIST");

        $unreadMessages = $entityManager->getRepository(Message::class)->countUnreadMessages($this->getUser()); // Récupère le nombre de messages non lus

        // Convertir la liste en un format associatif [sender_id => unread_count]
        $unreadCounts = [];
        foreach ($unreadMessages as $msgData) {
            $unreadCounts[$msgData['sender_id']] = $msgData['unread_count'];
        }

        foreach($archivists as $archivist){
            $archivist->isConnecte = $entityManager->getRepository(Utilisateur::class)->isUserConnecte($archivist->getId());
        }

        $userChatResponse = $this->render('user/chat-user.html.twig', [
            'user_chat' => 'UserChatPage',
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
            'nonLusCounts' => $unreadCounts,
            'archivistes' => $archivists
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
    #[IsGranted("ROLE_ARCHIVIST")]
    public function archchat(EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ARCHIVISTE]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);


        $users = $entityManager->getRepository(Utilisateur::class)->findAllExcept($this->getUser());

        $unreadMessages = $entityManager->getRepository(Message::class)->countUnreadMessages($this->getUser()); // Récupère le nombre de messages non lus

        // Convertir la liste en un format associatif [sender_id => unread_count]
        $unreadCounts = [];
        foreach ($unreadMessages as $msgData) {
            $unreadCounts[$msgData['sender_id']] = $msgData['unread_count'];
        }

        foreach($users as $user){
            $user->isConnecte = $entityManager->getRepository(Utilisateur::class)->isUserConnecte($user->getId());
        }

        $archivistChatResponse = $this->render('archivemanager/chat-archman.html.twig', [
            'archivist_chat' => 'ArchivistChatPage',
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
            'nonLusCounts' => $unreadCounts,
            'utilisateurs' => $users
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

    #[Route('/admin/chat', name: 'app_admin_chat')]
    #[IsGranted("ROLE_ADMIN")]
    public function adminchat(EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ADMINISTRATEUR]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $archivists = $entityManager->getRepository(Utilisateur::class)->findByRoles("ROLE_ARCHIVIST");

        $unreadMessages = $entityManager->getRepository(Message::class)->countUnreadMessages($this->getUser()); // Récupère le nombre de messages non lus

        // Convertir la liste en un format associatif [sender_id => unread_count]
        $unreadCounts = [];
        foreach ($unreadMessages as $msgData) {
            $unreadCounts[$msgData['sender_id']] = $msgData['unread_count'];
        }

        foreach($archivists as $archivist){
            $archivist->isConnecte = $entityManager->getRepository(Utilisateur::class)->isUserConnecte($archivist->getId());
        }

        $adminChatResponse = $this->render('administrator/chat-admin.html.twig', [
            'user_chat' => 'UserChatPage',
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
            'nonLusCounts' => $unreadCounts,
            'archivistes' => $archivists
        ]);
        $adminChatResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]); 
        return $adminChatResponse;
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/chat/send', name: 'app_chat_send', methods: ['POST'])]
    public function sendMessage(Request $request, EntityManagerInterface $entityManager, HubInterface $hub, Security $security, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $userO = $security->getUser();
        if (!$userO) {
            return new JsonResponse(['error' => 'Vous n\'êtes pas autorisé à consulter cette ressource'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $recipientId = $data['destinataire_id'] ?? null;
        $content = $data['contenu'] ?? '';

        if (!$recipientId || empty($content)) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $recipient = $utilisateurRepository->find($recipientId);

        if (!$recipient) {
            return new JsonResponse(['error' => 'Recipient not found'], 404);
        }

        $message = new Message();
        $message->setSender($this->getUser());
        $message->setRecipient($recipient);
        $message->setContent($content);
        $message->setStatut(StatutMessage::NON_LU);

        $entityManager->persist($message);
        $entityManager->flush();

        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $recipient->getId()]);

        $update = new Update(
            'http://127.0.0.1:8000/users/' . $recipient->getId(),
            json_encode([
                'type' => 'message',
                'notif' => 'Vous avez un nouveau message',
                'id' => $message->getId(),
                'sender' => $this->getUser()->getId(),
                'recipient' => $recipient->getId(),
                'content' => $message->getContent(),
                'createdAt' => $message->getDateCreation()->format('Y-m-d H:i:s'),
                'unread_msgs_count' => $nbrMsgsUnread,
            ]),
            true
        );

        $hub->publish($update);

        $msgForReceiver = 'Vous avez un nouveau message de '. $this->getUser()->getNomcomplet();

        $this->createChatNotificationForReceiver($msgForReceiver, $entityManager, $recipient);

        $referer = $request->headers->get('referer');

        $urlToRedirect = $referer;
        
        return new JsonResponse([
            'success' => true,
            'redirectUrl' => $urlToRedirect
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/chat/messages/{recipientId}', name: 'app_chat_messages', methods: ['GET'])]
    public function getMessages(MessageRepository $messageRepository, UtilisateurRepository $utilisateurRepository, Security $security, int $recipientId): JsonResponse
    {
        $userO = $security->getUser();
        if (!$userO) {
            return new JsonResponse(['error' => 'Vous n\'êtes pas autorisé à consulter cette ressource'], 401);
        }

        $currentUser = $this->getUser();
        $recipient = $utilisateurRepository->find($recipientId);

        if (!$recipient) {
            return new JsonResponse(['error' => 'Recipient not found'], 404);
        }

        $messages = $messageRepository->findByConversation($currentUser, $recipient);

            // Transformer les objets Message en tableau JSON avec les infos nécessaires
        $formattedMessages = array_map(function ($message) {
            return [
                'id' => $message->getId(),
                'contenu' => $message->getContent(),
                'envoyeur_id' => $message->getSender()->getId(), // Ici on ajoute l'ID de l'envoyeur
                'date_envoi' => $message->getDateCreation()->format('Y-m-d H:i:s')
            ];
        }, $messages);

        return new JsonResponse([
            'utilisateur_id' => $currentUser->getId(),
            'messages' => $formattedMessages
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/chat/mark-as-read/{senderId}', name: 'app_chat_mark_as_read', methods: ['POST'])]
    public function markMessagesAsRead(MessageRepository $messageRepository, EntityManagerInterface $entityManager, Security $security, int $senderId): JsonResponse
    {
        $userO = $security->getUser();
        if (!$userO) {
            return new JsonResponse(['error' => 'Vous n\'êtes pas autorisé à consulter cette ressource'], 401);
        }

        $user = $this->getUser();

        $messages = $messageRepository->findBy([
            'sender' => $senderId,
            'recipient' => $user,
            'statut' => StatutMessage::NON_LU
        ]);
    
        foreach ($messages as $message) {
            $message->setStatut(StatutMessage::LU);
        }
    
        $entityManager->flush();
    
        return new JsonResponse(['success' => true]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/status/update', name: 'app_user_status_update', methods: ['POST'])]
    public function updateUserStatus(EntityManagerInterface $entityManager, HubInterface $hub, Security $security, Request $request): JsonResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Vous n\'êtes pas autorisé à consulter cette ressource'], 401);
        }
    
        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? 'offline';   
        
        $user->setDerniereActiv(new \DateTime());
        $entityManager->flush();

        $update = new Update(
            'http://127.0.0.1:8000/status', 
            json_encode([
                'userId' => $user->getId(),
                'statut' => $status
            ])
        );
    
        $hub->publish($update);
    
        return new JsonResponse(['message' => 'Statut de connexion mis à jour']);
    }

    private function createChatNotificationForReceiver($message, EntityManagerInterface $entityManager, Utilisateur $recipient) {

        $notification = new Notification();
        $notification->setType(TypeNotification::MESSAGE);
        if(in_array("ROLE_ARCHIVIST", $recipient->getRoles())){
            $notification->setNiveauAcces(NiveauAccesNotification::ARCHIVISTE);
        }
        elseif(in_array("ROLE_USER", $recipient->getRoles())){
            $notification->setNiveauAcces(NiveauAccesNotification::UTILISATEUR);
        }
        elseif(in_array("ROLE_ADMIN", $recipient->getRoles())){
            $notification->setNiveauAcces(NiveauAccesNotification::ADMINISTRATEUR);
        }
        $notification->setStatut(StatutNotification::NON_LU);
        $notification->setMessage($message);
        $notification->setUtilisateur($recipient);

        $entityManager->persist($notification);
        $entityManager->flush();
    }

}
