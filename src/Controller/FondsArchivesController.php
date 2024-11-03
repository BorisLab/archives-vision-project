<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Entity\Fichier;
use App\Form\DossierType;
use App\Entity\Departement;
use App\Form\AddFichierNumType;
use App\Form\AddFichierPhysType;
use App\Repository\DossierRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
    public function fondsContent(Request $request, DossierRepository $dossierRepository, EntityManagerInterface $entityManager): Response
    {
        $formCreate = $this->createForm(DossierType::class, new Dossier());

        $deps = $entityManager->getRepository(Departement::class)->findAll();

        if($request->isMethod('POST')){
            $action = $request->request->get('_action');

            switch($action) {
                //Dossiers
                case 'createDossier':
                    return $this->handleCreateDossier($request, $entityManager);
                case 'editDossier':
                    return $this->handleEditDossier($request, $entityManager);
                case 'deleteDossier':
                    return $this->handleDeleteDossier($request, $entityManager);

                //Fichiers
                case 'editFichier':
                    return $this->handleEditFichier($request, $entityManager);
                case 'deleteFichier':
                    return $this->handleDeleteFichier($request, $entityManager);
            }
        }   

        $formats = '[{"name": "--Choisir--", "value": ""}, {"name": "Physique", "value": "Physique"}, {"name": "Numérique", "value": "Numérique"}, {"name": "Mixte", "value": "Mixte"}]';
        $statuts = '[{"name": "Disponible", "value": 1}, {"name": "Indisponible", "value": null}]';

        $fonds = $dossierRepository->findBy(['parent' => false]); 
        $fonds = $this->render('fonds_archives/fonds.html.twig', [
            'dossiers' => $fonds,
            'deps' => $deps,
            'formats' => json_decode($formats, true),
            'statuts' => json_decode($statuts, true),
            'fichiers' => [],
            'dossier_courant' => null,
            'dossierForm' => $formCreate->createView(),
        ]);
        $fonds->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);

        return $fonds;
    }

    private function handleCreateDossier(Request $request, EntityManagerInterface $entityManager) : Response {
        $dossier = new Dossier();
        $formCreate = $this->createForm(DossierType::class, $dossier);
        $formCreate->handleRequest($request);

            if ($formCreate->isSubmitted() && $formCreate->isValid()) {

                $dossier->setUtilisateur($this->getUser());

                $checkKeywords = $dossier->getTags();

                if($checkKeywords){
                    $keywords = array_map('trim', explode(',', $checkKeywords));
                    $uniqueKeywords = array_unique($keywords);
    
                    $dossier->setTags(implode(',', $uniqueKeywords));
                }
    
                $entityManager->persist($dossier);
                $entityManager->flush();

                $this->addFlash('folder_create_success', 'Dossier créé avec succès');
                return $this->redirectToRoute('app_archives_folders_directory');
            }
            elseif ($formCreate->isSubmitted() && !$formCreate->isValid()) {
                $this->addFlash('folder_create_error', 'Echec de la création du dossier');
                return $this->redirectToRoute('app_archives_folders_directory');
            }
    }

    private function updateDossierStatut(Dossier $dossier, string $statutDossier): void
    {
        // Appliquer le statut au dossier principal
        $dossier->setStatut($statutDossier);
    
        // Appliquer le statut aux sous-dossiers de manière récursive
        foreach ($dossier->getDossiers() as $sousDossier) {
            $this->updateDossierStatut($sousDossier, $statutDossier); // récursivité pour sous-dossiers
        }
    
        // Appliquer le statut à chaque fichier dans le dossier
        foreach ($dossier->getFichiers() as $fichier) {
            $fichier->setStatut($statutDossier);
        }
    }

    private function handleEditDossier(Request $request, EntityManagerInterface $entityManager) {
        $id = $request->request->get('folderId');
        $dossier = $entityManager->getRepository(Dossier::class)->find($id);

        if(!$dossier) {
            $this->addFlash('folder_edit_error', 'Dossier introuvable');
            return $this->redirectToRoute('app_archives_folders_directory');
        }
        else {
            $nomDossier = $request->request->get('libelle_dossier');
            $formatDossier = $request->request->get('format_dossier');
            $statutDossier = $request->request->get('statut_dossier');
            $depInput = $request->request->get('departement_dossier');
            $depEdit = $entityManager->getRepository(Departement::class)->findOneBy(['libelle_dep' => $depInput]);
            $keywords = $request->request->get('motsClesDossier');

        if(!$depEdit){
            $this->addFlash('folder_edit_error', 'Echec de la modification du dossier');
            return $this->redirectToRoute('app_archives_folders_directory');
        }

        else{
            if($nomDossier !== '' && $formatDossier !== ''){
                $dossier->setLibelleDossier($nomDossier);
                $dossier->setFormat($formatDossier);
                $dossier->setStatut($statutDossier);
                $dossier->setDepartement($depEdit);
                $dossier->setTags($keywords);

                $this->updateDossierStatut($dossier, $statutDossier);
    
                $entityManager->flush();
                $this->addFlash('folder_edit_success', 'Dossier modifié avec succès');
                return $this->redirectToRoute('app_archives_folders_directory');
            }
            else {
                $this->addFlash('folder_edit_error', 'Echec de la modification du dossier');
                return $this->redirectToRoute('app_archives_folders_directory');
            }
        }
    }
    }

    private function handleDeleteDossier(Request $request, EntityManagerInterface $entityManager) {
        $id = $request->request->get('folderId');
        $dossier = $entityManager->getRepository(Dossier::class)->find($id);

        if(!$dossier) {
            $this->addFlash('folder_delete_error', 'Dossier introuvable');
            return $this->redirectToRoute('app_archives_folders_directory');
        }

        else if(!$dossier->getDossiers()->isEmpty() || !$dossier->getFichiers()->isEmpty()){
            $this->addFlash('folder_delete_error', 'Ce dossier n\'est pas vide');
            return $this->redirectToRoute('app_archives_folders_directory');
        }

        $entityManager->remove($dossier);
        $entityManager->flush();
        $this->addFlash('folder_delete_success', 'Dossier supprimé avec succès');
        return $this->redirectToRoute('app_archives_folders_directory');
    }

    #[Route('/archivist/{id<[0-9]+>}/sous-fonds', name: 'app_archives_folder_content')]
    #[IsGranted("ROLE_ARCHIVIST")]
    public function sousFondsContent(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager, int $id) {
        $dossierForm = $this->createForm(DossierType::class, new Dossier());

        $deps = $entityManager->getRepository(Departement::class)->findAll();

        //Gestion des sous-dossiers
        $dossierCourant = $entityManager->getRepository(Dossier::class)->find($id);
        $dossierParent = $dossierCourant->getDossierParent();
        $dossiers = $dossierCourant ? $dossierCourant->getDossiers() : [];
        $arborescence = $dossierParent ? $dossierParent->getArborescence() : [];

            if($request->isMethod('POST') && ($request->request->get('_action') === 'createDossier')){
                $dossier = new Dossier();
                $dossierForm = $this->createForm(DossierType::class, $dossier);
                $dossierForm->handleRequest($request);
    
                if ($dossierForm->isSubmitted() && $dossierForm->isValid()) {
                    $dossier->setUtilisateur($this->getUser());
                    $dossierCourant->addDossier($dossier);
                    $entityManager->persist($dossier);

                    $entityManager->flush();
    
                    $this->addFlash('subfolder_create_success', 'Sous-dossier créé avec succès');
                    return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
                }
                elseif ($dossierForm->isSubmitted() && !$dossierForm->isValid()) {
                    $this->addFlash('subfolder_create_error', 'Echec de la création du sous-dossier');
                    return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
                }
            }
            else if($request->isMethod('POST') && ($request->request->get('_action') === 'editDossier')){
                $folderId = $request->request->get('folderId');
                $dossier = $entityManager->getRepository(Dossier::class)->find($folderId);
                $formEdit = $this->createForm(DossierType::class, $dossier);
                $formEdit->handleRequest($request);
        
                $nomDossier = $request->request->get('libelle_dossier');
                $formatDossier = $request->request->get('format_dossier');
                $statutDossier = $request->request->get('statut_dossier');
                $depInput = $request->request->get('departement_dossier');
                $depEdit = $entityManager->getRepository(Departement::class)->findOneBy(['libelle_dep' => $depInput]);
                $keywords = $request->request->get('motsClesDossier');
        
                if (!$depEdit){
                    $this->addFlash('subfolder_edit_error', 'Echec de la modification du sous-dossier');
                    return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);    
                }
                else {
                    if($nomDossier !== '' && $formatDossier !== ''){
                        $dossier->setLibelleDossier($nomDossier);
                        $dossier->setFormat($formatDossier);
                        $dossier->setStatut($statutDossier);
                        $dossier->setDepartement($depEdit);
                        $dossier->setTags($keywords);

                        $this->updateDossierStatut($dossier, $statutDossier);      

                        $entityManager->flush();     
                    
                        $this->addFlash('subfolder_edit_success', 'Sous-dossier modifié avec succès');
                        return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]); 
                    }
                    else{
                        $this->addFlash('subfolder_edit_error', 'Echec de la modification du sous-dossier');
                        return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);     
                    }
                }

            }
            else if($request->isMethod('POST') && ($request->request->get('_action') === 'deleteDossier')){
                $folderId = $request->request->get('folderId');
                $dossier = $entityManager->getRepository(Dossier::class)->find($folderId);
        
                if(!$dossier) {
                    $this->addFlash('subfolder_delete_error', 'Dossier introuvable');
                    return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
                }

                else if(!$dossier->getDossiers()->isEmpty() || !$dossier->getFichiers()->isEmpty()){
                    $this->addFlash('subfolder_delete_error', 'Ce dossier n\'est pas vide');
                    return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
                }
        
                $entityManager->remove($dossier);
                $entityManager->flush();
                $this->addFlash('subfolder_delete_success', 'Dossier supprimé avec succès');
            }

        $dossierRacine = $dossierCourant->getDossierRacine();

        //Gestions des fichiers
        $fichierNumForm = $this->createForm(AddFichierNumType::class, new Fichier());
        $fichierNumForm->handleRequest($request);

        $fichierPhys = new Fichier();
        $fichierPhysForm = $this->createForm(AddFichierPhysType::class, $fichierPhys);
        $fichierPhysForm->handleRequest($request);

        $fichiers = $dossierCourant ? $dossierCourant->getFichiers() : [];

        $allowedExtensions = [
            'Image' => ['jpg', 'jpeg', 'png', 'gif', 'apng', 'gif', 'svg'],
            'Vidéo' => ['mp4', 'avi', 'mov', 'mkv'],
            'Audio' => ['mp3', 'wav'],
            'Document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']
        ];

        //Création de fichiers numériques
        if ($fichierNumForm->isSubmitted() && $fichierNumForm->isValid()) {
            $loadedFiles = $fichierNumForm->get('fichiers')->getData();
            $uploadedFiles = [];
            $upload_success = false;

                foreach ($loadedFiles as $fichierNum) {
                    $originalNomFichier = pathinfo($fichierNum->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeNomFichier = $slugger->slug($originalNomFichier);
                    $newNomFichier = $safeNomFichier.'-'.uniqid().'.'.$fichierNum->guessExtension();
                    $ext = strtolower($fichierNum->guessExtension());

                    $typeFic = null;
                    foreach ($allowedExtensions as $type => $exts) {
                        if (in_array($ext, $exts)) {
                            $typeFic = $type;
                            break;
                        }
                    }

                    if ($typeFic === null) {
                        $this->addFlash('add_soft_file_error', 'Fichier non autorisé');
                        $uploadSuccess = false;
                        continue; // Skip this file and continue with the next one
                    }

                    // Move the file to the directory where files are stored
                    try {
                        $fichierNum->move(
                            $this->getParameter('files_dir'),
                            $newNomFichier
                        );

                        $filePath = $this->getParameter('files_dir') . '/'. $newNomFichier;
                        $uploadedFile = new Fichier();
                        $format = "Numérique";
                        $uploadedFile->setLibelleFichier($originalNomFichier);
                        $uploadedFile->setFormat($format);
                        $uploadedFile->setType($typeFic);
                        $dossierCourant->addFichier($uploadedFile);
                        $uploadedFile->setCheminAcces($filePath);
    
                        $entityManager->persist($uploadedFile);
                        $uploadedFiles[] = $uploadedFile;
                        $upload_success = true;

                    } catch (FileException $e) {
                        $this->addFlash('add_soft_file_error', 'Echec de l\'importation du/des fichier(s)!');
                        return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
                    }

                }
            if($upload_success) {
                $entityManager->flush();
                $this->addFlash('add_soft_file_success', 'Importation effectuée avec succès');
                return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
            }
        }

        elseif ($fichierNumForm->isSubmitted() && !$fichierNumForm->isValid()) {
            $this->addFlash('add_soft_file_error', 'Aucun fichier importé');
            return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
        }

        //Création de fichiers physiques
        if($fichierPhysForm->isSubmitted() && $fichierPhysForm->isValid()){
            $format = "Physique";
            $dossierCourant->addFichier($fichierPhys);
            $fichierPhys->setFormat($format);

            $checkKeywords = $fichierPhys->getTags();

            if($checkKeywords){
                $keywords = array_map('trim', explode(',', $checkKeywords));
                $uniqueKeywords = array_unique($keywords);

                $fichierPhys->setTags(implode(',', $uniqueKeywords));
            }

            $entityManager->persist($fichierPhys);
            $entityManager->flush();


            $this->addFlash('add_hard_file_success', 'Pièce ajoutée avec succès');
            return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
        }
        elseif ($fichierPhysForm->isSubmitted() && !$fichierPhysForm->isValid()) {
            $this->addFlash('add_hard_file_error', 'Echec de l\'ajout de la pièce');
            return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
        }
        
        //Edition de fichiers
        if($request->isMethod('POST') && ($request->request->get('_action') === 'editFichier')) {
            $fileId = $request->request->get('fileId');
            $fichier = $entityManager->getRepository(Fichier::class)->find($fileId);
    
            if(!$fichier) {
                $this->addFlash('file_edit_error', 'Fichier introuvable');
                return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
            }
            else {
                $nomFichier = $request->request->get('libelle_fichier');
                $keywords = $request->request->get('motsClesFichier');
                $statutFichier = $request->request->get('statut_fichier');
    
                if($nomFichier !== ''){
                    $fichier->setLibelleFichier($nomFichier);
                    $fichier->setTags($keywords);
                    $fichier->setLibelleFichier($nomFichier);
                    $fichier->setStatut($statutFichier);
    
                    $entityManager->flush();
                    $this->addFlash('file_edit_success', 'Fichier modifié avec succès');
                    return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
                }
                else {
                    $this->addFlash('file_edit_error', 'Echec de la modification du fichier');
                    return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
                }
            }
        }

        //Suppression de fichiers
        if($request->isMethod('POST') && ($request->request->get('_action') === 'deleteFichier')) {
            $fileId = $request->request->get('fileId');
            $fichier = $entityManager->getRepository(Fichier::class)->find($fileId);

            if(!$fichier) {
                $this->addFlash('file_delete_error', 'Fichier introuvable');
                return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
            }

            else {
                $filePath = $fichier->getCheminAcces();

                // Supprimer le fichier du répertoire
                if($fichier->getFormat() == "Numérique")
                {
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    } else {
                        $this->addFlash('file_delete_error', 'Chemin d\'accès inaccessible');
                        return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
                    }
                }
        
                $entityManager->remove($fichier);
                $entityManager->flush();
                $this->addFlash('file_delete_success', 'Fichier supprimé avec succès');
            }
            return $this->redirectToRoute('app_archives_folder_content', ['id' => $id]);
        }

        $formats = '[{"name": "--Choisir--", "value": ""}, {"name": "Physique", "value": "Physique"}, {"name": "Numérique", "value": "Numérique"}, {"name": "Mixte", "value": "Mixte"}]';
        $statuts = '[{"name": "Disponible", "value": 1}, {"name": "Indisponible", "value": null}]';

        $fichiersAndFolders = $this->render('fonds_archives/dossier-contenu.html.twig', [
            'dossiers' => $dossiers,
            'fichiers' => $fichiers,
            'formats' => json_decode($formats, true),
            'statuts' => json_decode($statuts, true),
            'deps' => $deps,
            'dossier_courant' => $dossierCourant,
            'dossier_racine' => $dossierRacine,
            'arborescence' => $arborescence,
            'dossierForm' => $dossierForm->createView(),
            'addNumFile' => $fichierNumForm->createView(),
            'addPhysFile' => $fichierPhysForm->createView(),

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

     /**
     * @Route("/file/preview/{id}", name="file_preview")
     */
    #[Route('archivist/file/preview/{id<[0-9]+>}', name: 'app_archives_file_preview')]
    #[IsGranted("ROLE_ARCHIVIST")]
    public function preview(int $id, EntityManagerInterface $entityManager): Response
    {
        $fichier = $entityManager->getRepository(Fichier::class)->find($id);

        if (!$fichier || $fichier->getFormat() === 'Physique') {
            throw $this->createNotFoundException('File not found or is not a digital file.');
        }

        $filePath = $fichier->getCheminAcces();
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('The file does not exist.');
        }

        // Determine content type based on file extension
        $mimeType = mime_content_type($filePath);

        return new Response(
            file_get_contents($filePath), // Serve the file content
            200, // Status code
            [
                'Content-Type' => $mimeType  // Set the correct content type
            ]
        );
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
