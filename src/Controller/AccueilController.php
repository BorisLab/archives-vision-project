<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Entity\Departement;
use App\Entity\DemandeAcces;
use App\Repository\DossierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccueilController extends AbstractController
{

    #[Route(['/user/home'], name: 'app_user_home')]
    #[IsGranted("ROLE_USER")]
    public function userhome(Request $request, DossierRepository $dossierRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $userDep = $user->getDepartement();
        $fonds = $dossierRepository->findBy(['parent' => false, 'departement' => $userDep]); 
        $reqAcces = $entityManager->getRepository(DemandeAcces::class)->findAll();
        $userHomeResponse = $this->render('user/fonds.html.twig', [
            'dossiers' => $fonds,
            'demandesacces' => $reqAcces,
            'fichiers' => [],
            'dossier_courant' => null,
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

    #[Route('/user/{id<[0-9]+>}/répertoire', name: 'app_user_folder_content')]
    #[IsGranted("ROLE_USER")]
    public function sousFondsContentForUser(Request $request, EntityManagerInterface $entityManager, int $id) {

        $deps = $entityManager->getRepository(Departement::class)->findAll();

        //Gestion des sous-dossiers
        $dossierCourant = $entityManager->getRepository(Dossier::class)->find($id);
        $dossierParent = $dossierCourant->getDossierParent();
        $dossiers = $dossierCourant ? $dossierCourant->getDossiers() : [];
        $arborescence = $dossierParent ? $dossierParent->getArborescence() : [];

        $dossierRacine = $dossierCourant->getDossierRacine();

        //Gestions des fichiers
        $fichiers = $dossierCourant ? $dossierCourant->getFichiers() : [];

        $formats = '[{"name": "--Choisir--", "value": ""}, {"name": "Physique", "value": "Physique"}, {"name": "Numérique", "value": "Numérique"}, {"name": "Mixte", "value": "Mixte"}]';
        $statuts = '[{"name": "Disponible", "value": 1}, {"name": "Indisponible", "value": null}]';
        $reqAcces = $entityManager->getRepository(DemandeAcces::class)->findAll();

        $fichiersAndFolders = $this->render('user/dossier-contenu.html.twig', [
            'dossiers' => $dossiers,
            'fichiers' => $fichiers,
            'formats' => json_decode($formats, true),
            'statuts' => json_decode($statuts, true),
            'deps' => $deps,
            'demandesacces' => $reqAcces,
            'dossier_courant' => $dossierCourant,
            'dossier_racine' => $dossierRacine,
            'arborescence' => $arborescence,

        ]);
        $fichiersAndFolders->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);

        return $fichiersAndFolders;
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
