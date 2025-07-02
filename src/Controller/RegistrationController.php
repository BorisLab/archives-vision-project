<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Utilisateur;
use App\Entity\Notification;
use App\Entity\StatutMessage;
use App\Entity\StatutUtilisateur;
use App\Entity\StatutNotification;
use App\Form\RegistrationFormType;
use App\Entity\NiveauAccesNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/admin/register-new-user', name: 'app_admin_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ADMINISTRATEUR]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $user->setStatut(StatutUtilisateur::INACTIF);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('inscription/admin-register-users.html.twig', [
            'registrationForm' => $form->createView(),
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread
        ]);
    }
}
