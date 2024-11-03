<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Form\DossierType;
use App\Entity\Departement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UsersController extends AbstractController
{
    #[Route('/users/list', name: 'app_users')]
    #[IsGranted("ROLE_USER")]
    public function userslist(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager) {

        $users->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);

        return $users;
    }
}
