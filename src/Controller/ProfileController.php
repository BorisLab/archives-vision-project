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
        $userProfileResponse = $this->render('user/profile-user.html.twig', [
            'user_profile' => 'UserProfilePage',
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
    public function archivistprofile(): Response
    {
        $archivistProfileResponse = $this->render('archivemanager/profile-archivist.html.twig', [
            'archivist_profile' => 'ArchivistProfilePage',
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
    public function adminprofile(): Response
    {
        $adminProfileResponse = $this->render('admin/profile-admin.html.twig', [
            'admin_profile' => 'AdminProfilePage',
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
}
