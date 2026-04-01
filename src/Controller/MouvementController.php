<?php

namespace App\Controller;

use App\Entity\Mouvement;
use App\Form\MouvementType;
use App\Repository\MouvementRepository;
use App\Service\AuditLogger;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/archivist/mouvements')]
#[IsGranted("ROLE_ARCHIVIST")]
class MouvementController extends AbstractController
{
    private AuditLogger $auditLogger;

    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    #[Route('/', name: 'app_mouvement_index', methods: ['GET'])]
    public function index(MouvementRepository $repository): Response
    {
        $mouvementsRecents = $repository->findRecent(100);
        $pretsEnCours = $repository->findPretsEnCours();
        $pretsEnRetard = $repository->findPretsEnRetard();

        return $this->render('archivemanager/mouvements/index.html.twig', [
            'mouvements_recents' => $mouvementsRecents,
            'prets_en_cours' => $pretsEnCours,
            'prets_en_retard' => $pretsEnRetard,
        ]);
    }

    #[Route('/new', name: 'app_mouvement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mouvement = new Mouvement();
        $mouvement->setUtilisateur($this->getUser());
        
        $form = $this->createForm(MouvementType::class, $mouvement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation : au moins un fichier ou dossier doit être sélectionné
            if (!$mouvement->getFichier() && !$mouvement->getDossier()) {
                $this->addFlash('error', 'Vous devez sélectionner au moins un fichier ou un dossier');
                return $this->render('archivemanager/mouvements/new.html.twig', [
                    'mouvement' => $mouvement,
                    'form' => $form,
                ]);
            }

            // Vérifier si c'est un prêt en retard
            if ($mouvement->getTypeMouvement() === 'pret' && $mouvement->isEnRetard()) {
                $mouvement->setStatut('en_retard');
            }

            $entityManager->persist($mouvement);
            
            // Mise à jour automatique du statut du fichier/dossier selon le type de mouvement
            $this->updateEntityStatus($mouvement);
            
            $entityManager->flush();

            // Audit log
            $this->auditLogger->logCreate('Mouvement', $mouvement->getId(), [
                'type' => $mouvement->getTypeMouvement(),
                'fichier_id' => $mouvement->getFichier()?->getFichierId(),
                'dossier_id' => $mouvement->getDossier()?->getDossierId(),
                'date_mouvement' => $mouvement->getDateMouvement()->format('Y-m-d H:i:s'),
            ]);

            $this->addFlash('success', 'Mouvement enregistré avec succès');
            return $this->redirectToRoute('app_mouvement_index');
        }

        return $this->render('archivemanager/mouvements/new.html.twig', [
            'mouvement' => $mouvement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mouvement_show', methods: ['GET'])]
    public function show(Mouvement $mouvement): Response
    {
        return $this->render('archivemanager/mouvements/show.html.twig', [
            'mouvement' => $mouvement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mouvement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Mouvement $mouvement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MouvementType::class, $mouvement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour automatique du statut du fichier/dossier si le mouvement change
            $this->updateEntityStatus($mouvement);
            
            $entityManager->flush();

            $this->auditLogger->logUpdate('Mouvement', $mouvement->getId(), [
                'type' => $mouvement->getTypeMouvement(),
                'statut' => $mouvement->getStatut(),
            ]);

            $this->addFlash('success', 'Mouvement modifié avec succès');
            return $this->redirectToRoute('app_mouvement_index');
        }

        return $this->render('archivemanager/mouvements/edit.html.twig', [
            'mouvement' => $mouvement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/reintegrer', name: 'app_mouvement_reintegrer', methods: ['POST'])]
    public function reintegrer(Request $request, Mouvement $mouvement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('reintegrer'.$mouvement->getId(), $request->request->get('_token'))) {
            if ($mouvement->getTypeMouvement() !== 'pret') {
                $this->addFlash('error', 'Seuls les prêts peuvent être réintégrés');
                return $this->redirectToRoute('app_mouvement_index');
            }

            // Créer un mouvement de réintégration
            $reintegration = new Mouvement();
            $reintegration->setTypeMouvement('reintegration');
            $reintegration->setDateMouvement(new \DateTime());
            $reintegration->setUtilisateur($this->getUser());
            $reintegration->setFichier($mouvement->getFichier());
            $reintegration->setDossier($mouvement->getDossier());
            $reintegration->setObservations('Réintégration suite au prêt #' . $mouvement->getId());
            $reintegration->setStatut('termine');

            // Marquer le prêt comme terminé
            $mouvement->setStatut('termine');
            $mouvement->setDateRetourEffective(new \DateTime());

            $entityManager->persist($reintegration);
            
            // Mise à jour automatique du statut du fichier/dossier pour la réintégration
            $this->updateEntityStatus($reintegration);
            
            $entityManager->flush();

            $this->auditLogger->logCreate('Mouvement', $reintegration->getId(), [
                'type' => 'reintegration',
                'pret_origine_id' => $mouvement->getId(),
                'fichier_id' => $reintegration->getFichier()?->getFichierId(),
                'dossier_id' => $reintegration->getDossier()?->getDossierId(),
            ]);

            $this->addFlash('success', 'Réintégration effectuée avec succès');
        }

        return $this->redirectToRoute('app_mouvement_index');
    }

    #[Route('/{id}', name: 'app_mouvement_delete', methods: ['POST'])]
    public function delete(Request $request, Mouvement $mouvement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mouvement->getId(), $request->request->get('_token'))) {
            $this->auditLogger->logDelete('Mouvement', $mouvement->getId(), [
                'type' => $mouvement->getTypeMouvement(),
                'date_mouvement' => $mouvement->getDateMouvement()->format('Y-m-d H:i:s'),
            ]);

            $entityManager->remove($mouvement);
            $entityManager->flush();

            $this->addFlash('success', 'Mouvement supprimé avec succès');
        }

        return $this->redirectToRoute('app_mouvement_index');
    }

    #[Route('/prets/en-retard', name: 'app_mouvement_retards', methods: ['GET'])]
    public function pretsEnRetard(MouvementRepository $repository): Response
    {
        $pretsEnRetard = $repository->findPretsEnRetard();

        return $this->render('archivemanager/mouvements/retards.html.twig', [
            'prets_en_retard' => $pretsEnRetard,
        ]);
    }

    #[Route('/statistiques', name: 'app_mouvement_stats', methods: ['GET'])]
    public function statistiques(Request $request, MouvementRepository $repository): Response
    {
        $dateDebut = $request->query->get('date_debut') 
            ? new \DateTime($request->query->get('date_debut'))
            : new \DateTime('-30 days');
        
        $dateFin = $request->query->get('date_fin')
            ? new \DateTime($request->query->get('date_fin'))
            : new \DateTime();

        $statistiques = $repository->getStatistiquesPeriode($dateDebut, $dateFin);

        return $this->render('archivemanager/mouvements/statistiques.html.twig', [
            'statistiques' => $statistiques,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ]);
    }

    /**
     * Met à jour automatiquement le statut de l'entité liée (Fichier ou Dossier)
     * en fonction du type de mouvement
     * 
     * Règles métier :
     * - Arrivage / Réintégration → statut = true (disponible)
     * - Prêt / Consultation → statut = false (indisponible)
     * - Déplacement → statut inchangé (simple relocalisation)
     */
    private function updateEntityStatus(Mouvement $mouvement): void
    {
        // Déterminer le nouveau statut selon le type de mouvement
        $nouveauStatut = match($mouvement->getTypeMouvement()) {
            'arrivage', 'reintegration' => true,  // Disponible
            'pret', 'consultation' => false,       // Indisponible
            'deplacement' => null,                 // Pas de changement
            default => null
        };

        // Si pas de changement de statut requis, on sort
        if ($nouveauStatut === null) {
            return;
        }

        // Appliquer le nouveau statut sur le fichier ou le dossier concerné
        if ($mouvement->getFichier()) {
            $mouvement->getFichier()->setStatut($nouveauStatut);
        }

        if ($mouvement->getDossier()) {
            $mouvement->getDossier()->setStatut($nouveauStatut);
        }
    }
}
