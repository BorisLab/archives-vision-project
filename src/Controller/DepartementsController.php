<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Departement;
use App\Entity\Notification;
use App\Entity\StatutMessage;
use App\Form\DepartementType;
use App\Entity\StatutNotification;
use App\Entity\NiveauAccesNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DepartementsController extends AbstractController
{
    #[Route('/admin/departements', name: 'app_admin_departements')]
    public function depslist(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::ADMINISTRATEUR]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $departements = $entityManager->getRepository(Departement::class)->findAll();

        $formCreate = $this->createForm(DepartementType::class, new Departement());

        if($request->isMethod('POST')){
            $action = $request->request->get('_action');

            switch($action) {
                //departements
                case 'createDepartement':
                    return $this->handleCreateDepartement($request, $entityManager);
                case 'editDepartement':
                    return $this->handleEditDepartement($request, $entityManager);
                case 'deleteDepartement':
                    return $this->handleDeleteDepartement($request, $entityManager);
            }
        }  

        foreach($departements as $dp){
          $dp->nbreUsers = $dp->getUtilisateurs()->count([]);
        }

        $adminDeps = $this->render('administrator/departements.html.twig', [
            'admin_deps_home' => 'AdminDepsPage',
            'deps' => $departements,
            'departementForm' => $formCreate->createView(),
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread
        ]);

        $adminDeps->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]);

        return $adminDeps;
    }

    private function handleCreateDepartement(Request $request, EntityManagerInterface $entityManager) : Response {
        $departement = new Departement();
        $formCreate = $this->createForm(DepartementType::class, $departement);
        $formCreate->handleRequest($request);

            if ($formCreate->isSubmitted() && $formCreate->isValid()) {
    
                $entityManager->persist($departement);
                $entityManager->flush();

                $this->addFlash('dep_create_success', 'Département créé avec succès');
                return $this->redirectToRoute('app_admin_departements');
            }
            elseif ($formCreate->isSubmitted() && !$formCreate->isValid()) {
                $this->addFlash('dep_create_error', 'Echec de la création du département');
                return $this->redirectToRoute('app_admin_departements');
            }
    }

    private function handleEditdepartement(Request $request, EntityManagerInterface $entityManager) {
        $id = $request->request->get('depId');
        $departement = $entityManager->getRepository(Departement::class)->find($id);

        if(!$departement) {
            $this->addFlash('dep_edit_error', 'Département introuvable');
            return $this->redirectToRoute('app_admin_departements');
        }
        else {
            $nomDep = $request->request->get('libelle_departement');
            $depParentInput = $request->request->get('departement_parent_departement');
            $depParentEdit = $entityManager->getRepository(Departement::class)->findOneBy(['libelle_dep' => $depParentInput]);

            if($nomDep !== ''){
                $departement->setLibelleDep($nomDep);
                
                if($depParentEdit){
                    $departement->setDepartementParent($depParentEdit);
                    $departement->setParent(true);
                } else {
                    $departement->setDepartementParent(NULL);
                    $departement->setParent(false);
                }
    
                $entityManager->flush();
                $this->addFlash('dep_edit_success', 'Département modifié avec succès');
                return $this->redirectToRoute('app_admin_departements');
            }
            else {
                $this->addFlash('dep_edit_error', 'Echec de la modification du département');
                return $this->redirectToRoute('app_admin_departements');
            }
        }
    }

    private function handleDeleteDepartement(Request $request, EntityManagerInterface $entityManager) {
        $id = $request->request->get('depId');
        $departement = $entityManager->getRepository(Departement::class)->find($id);

        $usersDepartement = $departement->getUtilisateurs()->count([]);

        if(!$departement) {
            $this->addFlash('dep_delete_error', 'Département introuvable');
            return $this->redirectToRoute('app_admin_departements');
        }

        else if($usersDepartement !== 0){
            $this->addFlash('dep_delete_error', 'Ce département n\'est pas vide');
            return $this->redirectToRoute('app_admin_departements');
        }

        $entityManager->remove($departement);
        $entityManager->flush();
        $this->addFlash('dep_delete_success', 'Département supprimé avec succès');
        return $this->redirectToRoute('app_admin_departements');
    }
}
