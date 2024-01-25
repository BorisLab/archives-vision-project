<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class DepartementsController extends AbstractController
{
    #[Route('/admin/departements', name: 'app_departements')]
    public function index(): Response
    {
        return $this->render('administrator/departements.html.twig', [
            'controller_name' => 'DepartementsController',
        ]);
    }
}
