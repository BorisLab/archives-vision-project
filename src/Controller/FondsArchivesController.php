<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class FondsArchivesController extends AbstractController
{
    #[Route('/archivist/archives', name: 'app_archives')]
    #[IsGranted("ROLE_ARCHIVIST")]
    public function archivmanage(): Response
    {
        return $this->render('fonds_archives/index.html.twig', [
            'archivist_archiv' => 'ArchivistFondsArchivesPage',
        ]);
    }

    #[Route('/admin/archives', name: 'app_admin_archives')]
    #[IsGranted("ROLE_ADMIN")]
    public function adminarchivmanage(): Response
    {
        return $this->render('administrator/archives.html.twig', [
            'admin_archiv' => 'AdminFondsArchivesPage',
        ]);
    }
}
