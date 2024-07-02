<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AccueilController extends AbstractController
{

    #[Route(['/user/home'], name: 'app_user_home')]
    #[IsGranted("ROLE_USER")]
    public function userhome(): Response
    {
        $userHomeResponse = $this->render('user/index.html.twig', [
            'user_home' => 'UserHomePage',
        ]);

        $userHomeResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);   

        return $userHomeResponse;
    }

    #[Route(['/archivist/home'], name: 'app_archivist_home')]
    #[IsGranted("ROLE_ARCHIVIST")]
    public function archivisthome(): Response
    {
        $archivistHomeResponse = $this->render('archivemanager/index.html.twig', [
            'archivist_home' => 'ArchivistHomePage',
        ]);

        $archivistHomeResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);   

        return $archivistHomeResponse;
    }

    #[Route(['/admin/home'], name: 'app_admin_home')]
    #[IsGranted("ROLE_ADMIN")]
    public function adminhome(): Response
    {
        $adminHomeResponse = $this->render('administrator/index.html.twig', [
            'admin_home' => 'AdminHomePage',
        ]);

        $adminHomeResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]); 

        return $adminHomeResponse;
    }
}
