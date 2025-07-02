<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Departement;
use App\Entity\Utilisateur;
use App\Entity\Notification;
use App\Entity\StatutMessage;
use App\Entity\StatutNotification;
use App\Entity\NiveauAccesNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileController extends AbstractController
{
    #[Route('/user/profile', name: 'app_user_profile')]
    #[IsGranted("ROLE_USER")]
    public function userprofile(       
       EntityManagerInterface $entityManager, 
       Request $request, 
       UserPasswordHasherInterface $userPasswordHasher, 
       TokenStorageInterface $tokenStorage,
       UserAuthenticatorInterface $userAuthenticator,
       UserProviderInterface $userProvider,
       UserCheckerInterface $userChecker): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::UTILISATEUR, 'utilisateur' => $this->getUser()->getId()]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $prenoms = $request->request->get('prenoms');

            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');


            $user->setNom($nom);
            $user->setPrenoms($prenoms);

            // Vérification et mise à jour du mot de passe si renseigné
            if (!empty($password) || !empty($confirmPassword)) {
                if ($password !== $confirmPassword) {
                    $this->addFlash('profile_infos_update_error', 'Les deux mots de passe ne correspondent pas.');
                    return $this->redirectToRoute('app_user_profile');
                }

                $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            }

            $entityManager->flush();

        // Recharger l'utilisateur depuis la base de données
        $refreshedUser = $userProvider->loadUserByIdentifier($user->getUserIdentifier());
        $userChecker->checkPreAuth($refreshedUser); // Vérification de l'état du compte

        // Création d'un nouveau token et mise à jour de la session
        $token = new UsernamePasswordToken($refreshedUser, 'main', $refreshedUser->getRoles());
        $tokenStorage->setToken($token);

            $this->addFlash('profile_infos_update_success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_user_profile');
        }

        $userProfileResponse = $this->render('user/profile-user.html.twig', [
            'user_profile' => 'UserProfilePage',
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
        ]);
        $userProfileResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]); 

        return $userProfileResponse;
    }

    #[Route('/archivist/profile', name: 'app_archivist_profile')]
    #[IsGranted("ROLE_ARCHIVIST")]
    public function archivistprofile(       
       EntityManagerInterface $entityManager, 
       Request $request, 
       UserPasswordHasherInterface $userPasswordHasher, 
       TokenStorageInterface $tokenStorage,
       UserAuthenticatorInterface $userAuthenticator,
       UserProviderInterface $userProvider,
       UserCheckerInterface $userChecker): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ARCHIVISTE]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $prenoms = $request->request->get('prenoms');

            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');


            $user->setNom($nom);
            $user->setPrenoms($prenoms);

            // Vérification et mise à jour du mot de passe si renseigné
            if (!empty($password) || !empty($confirmPassword)) {
                if ($password !== $confirmPassword) {
                    $this->addFlash('profile_infos_update_error', 'Les deux mots de passe ne correspondent pas.');
                    return $this->redirectToRoute('app_archivist_profile');
                }

                $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            }

            $entityManager->flush();

        // Recharger l'utilisateur depuis la base de données
        $refreshedUser = $userProvider->loadUserByIdentifier($user->getUserIdentifier());
        $userChecker->checkPreAuth($refreshedUser); // Vérification de l'état du compte

        // Création d'un nouveau token et mise à jour de la session
        $token = new UsernamePasswordToken($refreshedUser, 'main', $refreshedUser->getRoles());
        $tokenStorage->setToken($token);

            $this->addFlash('profile_infos_update_success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_archivist_profile');
        }

        $archivistProfileResponse = $this->render('archivemanager/profile-archivist.html.twig', [
            'archivist_profile' => 'ArchivistProfilePage',
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread
        ]);
        $archivistProfileResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]); 

        return $archivistProfileResponse;
    }

    #[Route('/admin/profile', name: 'app_admin_profile')]
    #[IsGranted("ROLE_ADMIN")]
    public function adminprofile(
       EntityManagerInterface $entityManager, 
       Request $request, 
       UserPasswordHasherInterface $userPasswordHasher, 
       TokenStorageInterface $tokenStorage,
       UserAuthenticatorInterface $userAuthenticator,
       UserProviderInterface $userProvider,
       UserCheckerInterface $userChecker): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ARCHIVISTE]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);
        $departements = $entityManager->getRepository(Departement::class)->findAll();

        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $prenoms = $request->request->get('prenoms');
            $email = $request->request->get('email');
            $idDepartement = $request->request->get('departement');
            $departement = $entityManager->getRepository(Departement::class)->findOneBy(['id' => $idDepartement]);

            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            // Vérification de l'unicité de l'email si modifié
            if ($email !== $user->getEmail() && $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email])) {
                $this->addFlash('profile_infos_update_error', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('app_admin_profile');
            }

            $user->setNom($nom);
            $user->setPrenoms($prenoms);
            $user->setEmail($email);
            $user->setDepartement($departement);

            // Vérification et mise à jour du mot de passe si renseigné
            if (!empty($password) || !empty($confirmPassword)) {
                if ($password !== $confirmPassword) {
                    $this->addFlash('profile_infos_update_error', 'Les deux mots de passe ne correspondent pas.');
                    return $this->redirectToRoute('app_admin_profile');
                }

                $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            }

            $entityManager->flush();

        // Recharger l'utilisateur depuis la base de données
        $refreshedUser = $userProvider->loadUserByIdentifier($user->getUserIdentifier());
        $userChecker->checkPreAuth($refreshedUser); // Vérification de l'état du compte

        // Création d'un nouveau token et mise à jour de la session
        $token = new UsernamePasswordToken($refreshedUser, 'main', $refreshedUser->getRoles());
        $tokenStorage->setToken($token);

            $this->addFlash('profile_infos_update_success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_admin_profile');
        }

        $adminProfileResponse = $this->render('administrator/profile-admin.html.twig', [
            'admin_profile' => 'AdminProfilePage',
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
            'deps' => $departements
        ]);
        $adminProfileResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]); 

        return $adminProfileResponse;
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/profile/update-photo', name: 'app_profile_update_photo', methods: ['POST'])]
    public function updatePhoto(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): JsonResponse
    {
        $user = $this->getUser();
        $photoFile = $request->files->get('photoProfil');
    
        if ($photoFile) {
            $newFilename = $slugger->slug(pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME))
                . '-' . uniqid() . '.' . $photoFile->guessExtension();
    
            try {
                $photoFile->move($this->getParameter('profile_pictures_directory'), $newFilename);
                $user->setPhotoProfil($newFilename);
                $entityManager->flush();

                $this->addFlash('profile_pic_update_success', 'Photo de profil mise à jour');
    
                return new JsonResponse([
                    'success' => true,
                    'newImageUrl' => $this->getParameter('profile_pictures_web_path') . '/' . $newFilename
                ]);
            } catch (FileException $e) {
                $this->addFlash('profile_pic_update_error', 'Echec de la mise à jour de la photo');

                return new JsonResponse(['success' => false], 500);
            }
        }
        $this->addFlash('profile_pic_update_error', 'Echec de la mise à jour de la photo');

        return new JsonResponse(['success' => false], 400);
    }
}
