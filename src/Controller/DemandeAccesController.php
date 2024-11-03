<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Entity\Fichier;
use App\Form\DossierType;
use App\Entity\Departement;
use App\Entity\Utilisateur;
use App\Entity\DemandeAcces;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DemandeAccesController extends AbstractController
{
    #[Route('/folder/request-access', name: 'app_request_folder_access', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function requestAccessFolder(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if($request->isMethod('POST') && ($request->request->get('_action') === 'reqDossier')) {
            $id = $request->request->get('reqDossierId');
            $dossier = $entityManager->getRepository(Dossier::class)->find($id);

            if (!$dossier ) {
               throw $this->createNotFoundException('Dossier introuvable.');
            }

            $req = new DemandeAcces();
            $req->setUtilisateur($this->getUser());
            $req->setDossier($dossier);
            $req->setStatut('pending');

            $entityManager->persist($req);
            $entityManager->flush();
        }

        // Notifier le gestionnaire avec Mercure après l’insertion (implémenté plus bas)
        
        return new JsonResponse(['success' => true]);
    }

    #[Route('user/file/request-access/{id<[0-9]+>}', name: 'app_request_file_access')]
    #[IsGranted("ROLE_USER")]
    public function requestAccessFile(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $fichier = $entityManager->getRepository(Fichier::class)->find($id);

        if (!$fichier ) {
            throw $this->createNotFoundException('Fichier introuvable.');
        }

        $req = new DemandeAcces();
        $req->setUtilisateur($this->getUser());
        $req->setFichier($fichier);
        $req->setStatut('pending');

        $entityManager->persist($req);
        $entityManager->flush();

        // Notifier le gestionnaire avec Mercure après l’insertion (implémenté plus bas)
        
        return new JsonResponse(['success' => true]);
    }
}
