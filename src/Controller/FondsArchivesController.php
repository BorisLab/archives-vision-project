<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Entity\Fichier;
use App\Form\AddFichierType;
use App\Form\CreateFolderType;
use App\Repository\DossierRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FondsArchivesController extends AbstractController
{
    #[Route('/archivist/archives2', name: 'app_archives_2')]
    #[IsGranted("ROLE_ARCHIVIST")]
    public function archivmanage(): Response
    {
        $archivistArchivResponse = $this->render('fonds_archives/index.html.twig', [
            'archivist_archiv' => 'ArchivistFondsArchivesPage',
        ]);
        $archivistArchivResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);

        return $archivistArchivResponse;
    }

    #[Route('/archivist/fonds', name: 'app_archives_folders_directory')]
    #[IsGranted("ROLE_ARCHIVIST")]
    public function listSousFonds(Request $request, DossierRepository $dossierRepository, EntityManagerInterface $entityManager): Response
    {
        $dossier = $request->get('dossier') ?? new Dossier();
        $form = $this->createForm(CreateFolderType::class, $dossier);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $dossier->setUtilisateur($this->getUser());

                $checkKeywords = $dossier->getTags();

                if($checkKeywords){
                    $keywords = array_map('trim', explode(',', $checkKeywords));
                    $uniqueKeywords = array_unique($keywords);
    
                    $dossier->setTags(implode(',', $uniqueKeywords));
                }
    
                $entityManager->persist($dossier);
                $entityManager->flush();

                // Échappement des caractères spéciaux pour éviter l'injection
                $nomDossier = htmlspecialchars($dossier->getLibelleDossier(), ENT_QUOTES, 'UTF-8');


                $this->addFlash('folder_create_success', 'Dossier <strong>' . $nomDossier . '</strong> créé avec succès');
                return $this->redirectToRoute('app_archives_folders_directory');
            }
            elseif ($form->isSubmitted() && !$form->isValid()) {
                $this->addFlash('folder_create_error', 'La création du nouveau dossier a échouée');
                return $this->redirectToRoute('app_archives_folders_directory');
            }
        }
        else {
            $form = $this->createForm(CreateFolderType::class, new Dossier());
        }

        $parent = null;
        $sousFonds = $dossierRepository->findBy(['parent' => false]); 
        $sousFondsList = $this->render('fonds_archives/fonds.html.twig', [
            'dossiers' => $sousFonds,
            'parent' => $parent,
            'createFolder' => $form->createView(),
        ]);
        $sousFondsList->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);

        return $sousFondsList;
    }

    #[Route('/archivist/sous-fonds/{id<[0-9]+>}', name: 'app_archives_folder_content')]
    #[IsGranted("ROLE_ARCHIVIST")]
    public function showFolderContent(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager, $id) {

        //Gestion des sous-dossiers
        $parent = $entityManager->getRepository(Dossier::class)->find($id);
        $dossiers = $parent ? $parent->getDossiers() : [];

        if ($request->isMethod('POST')) {
            $dossier = new Dossier();
            $dossierForm = $this->createForm(CreateFolderType::class, $dossier);
            $dossierForm->handleRequest($request);

            if ($dossierForm->isSubmitted() && $dossierForm->isValid()) {

                $dossier->setUtilisateur($this->getUser());
                $parent->addDossier($dossier);
                $entityManager->persist($dossier);
                $entityManager->flush();

                $this->addFlash('success', 'Sous-Dossier créé avec succès');
                return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
            }
            elseif ($dossierForm->isSubmitted() && !$dossierForm->isValid()) {
                $this->addFlash('folder_create_error', 'La création du nouveau sous-dossier a échouée');
                return $this->redirectToRoute('app_archives_folders_directory', ['id' => $id]);
            }
        } else {
            $dossierForm = $this->createForm(CreateFolderType::class, new Dossier());
        }
        $dossierRacine = $parent->getDossierRacine();

        //Gestions des fichiers
        $fichierForm = $this->createForm(AddFichierType::class);
        $fichierForm->handleRequest($request);
        $fichiers = $parent ? $parent->getFichiers() : [];

        if ($fichierForm->isSubmitted() && $fichierForm->isValid()) {
            /** @var UploadedFile fichier */
            $fichier = $fichierForm->get('fichier')->getData();
            $uploadedFichiers = [];

            foreach ($fichiers as $fichier) {
                if ($fichier) {
                    $originalNomFichier = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeNomFichier = $slugger->slug($originalNomFichier);
                    $newNomFichier = $safeNomFichier.'-'.uniqid().'.'.$fichier->guessExtension();

                    // Move the file to the directory where files are stored
                    try {
                        $fichier->move(
                            $this->getParameter('files_directory'),
                            $newNomFichier
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Failed to upload file.');
                        return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
                    }

                    // Save the file information in the database
                    $uploadedFichier = new Fichier();
                    $uploadedFichier-> setLibelleFichier($newNomFichier);
                    $parent->addDossier($uploadedFichier);
                    $uploadedFichier->setCheminAcces($originalNomFichier);

                    $entityManager->persist($uploadedFichier);
                    $uploadedFichiers[] = $uploadedFichier;
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'File(s) uploaded successfully!');
            return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
        }

        $fichiersAndFolders = $this->render('fonds_archives/dossier-contenu.html.twig', [
            'dossiers' => $dossiers,
            'fichiers' => $fichiers,
            'parent' => $parent,
            'dossier_racine' => $dossierRacine,
            'createFolder' => $dossierForm->createView(),
            'addFile' => $fichierForm->createView(),
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


    #[Route('/admin/archives', name: 'app_admin_archives')]
    #[IsGranted("ROLE_ADMIN")]
    public function adminarchivmanage(): Response
    {
        return $this->render('administrator/archives.html.twig', [
            'admin_archiv' => 'AdminFondsArchivesPage',
        ]);
    }
}
