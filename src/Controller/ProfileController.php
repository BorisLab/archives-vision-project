<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/user/profile', name: 'app_user_profile')]
    #[IsGranted("ROLE_USER")]
    public function userprofile(): Response
    {
        return $this->render('user/profile-user.html.twig', [
            'user_profile' => 'UserProfilePage',
        ]);
    }

    #[Route('/archivist/profile', name: 'app_archivist_profile')]
    #[IsGranted("ROLE_ARCHIVIST")]
    public function archivistprofile(): Response
    {
        return $this->render('archivemanager/profile-archivist.html.twig', [
            'archivist_profile' => 'ArchivistProfilePage',
        ]);
    }

    #[Route('/admin/profile', name: 'app_admin_profile')]
    #[IsGranted("ROLE_ADMIN")]
    public function adminprofile(): Response
    {
        return $this->render('admin/profile-admin.html.twig', [
            'admin_profile' => 'AdminProfilePage',
        ]);
    }
}
