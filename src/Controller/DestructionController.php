<?php

namespace App\Controller;

use App\Entity\DemandeDestruction;
use App\Entity\Dossier;
use App\Entity\Fichier;
use App\Repository\DemandeDestructionRepository;
use App\Service\AuditLogger;
use App\Service\PdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mercure\Authorization;
use App\Entity\Message;
use App\Entity\StatutNotification;
use App\Entity\StatutMessage;
use App\Entity\Notification;
use App\Entity\NiveauAccesNotification;


class DestructionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DemandeDestructionRepository $demandeDestructionRepository,
        private AuditLogger $audit,
        private PdfGenerator $pdfGenerator
    ) {
    }

    #[Route('/archivist/destruction/demander', name: 'app_destruction_demander', methods: ['POST'])]
    #[IsGranted('ROLE_ARCHIVIST')]
    public function demander(Request $request): JsonResponse
    {
        $typeEntite = $request->request->get('type_entite'); // 'Dossier' ou 'Fichier'
        $entiteId = $request->request->get('entite_id');
        $justification = $request->request->get('justification');

        if (!$typeEntite || !$entiteId || !$justification) {
            return $this->json(['success' => false, 'message' => 'Paramètres manquants'], 400);
        }

        // Récupérer l'entité et son libellé
        $libelleEntite = '';
        if ($typeEntite === 'Dossier') {
            $dossier = $this->entityManager->getRepository(Dossier::class)->find($entiteId);
            if (!$dossier) {
                return $this->json(['success' => false, 'message' => 'Dossier introuvable'], 404);
            }
            $libelleEntite = $dossier->getLibelleDossier();
        } elseif ($typeEntite === 'Fichier') {
            $fichier = $this->entityManager->getRepository(Fichier::class)->find($entiteId);
            if (!$fichier) {
                return $this->json(['success' => false, 'message' => 'Fichier introuvable'], 404);
            }
            $libelleEntite = $fichier->getLibelleFichier();
        }

        $demande = new DemandeDestruction();
        $demande->setTypeEntite($typeEntite);
        $demande->setEntiteId($entiteId);
        $demande->setLibelleEntite($libelleEntite);
        $demande->setDemandeur($this->getUser());
        $demande->setJustification($justification);

        $this->entityManager->persist($demande);
        $this->entityManager->flush();

        $this->audit->logCreate('DemandeDestruction', $demande->getId(), [
            'type_entite' => $typeEntite,
            'entite_id' => $entiteId,
            'libelle' => $libelleEntite,
        ]);

        return $this->json([
            'success' => true,
            'message' => 'Demande de destruction créée avec succès',
            'demande_id' => $demande->getId(),
        ]);
    }

    #[Route('/admin/destruction/demandes', name: 'app_destruction_liste', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function listeDemandes(Request $request, EntityManagerInterface $entityManager, Authorization $authorization): Response
    {
        $statut = $request->query->get('statut');
        $demandeurId = $request->query->get('demandeur_id');

        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::UTILISATEUR, 'utilisateur' => $this->getUser()->getId()]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $demandes = $this->demandeDestructionRepository->findWithFilters($statut, $demandeurId);
        $stats = $this->demandeDestructionRepository->countByStatut();

        return $this->render('destruction/liste_demandes.html.twig', [
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
            'demandes' => $demandes,
            'stats' => $stats,
            'filters' => [
                'statut' => $statut,
                'demandeur_id' => $demandeurId,
            ],
        ]);
    }

    #[Route('/admin/destruction/{id}/approuver', name: 'app_destruction_approuver', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function approuver(Request $request, DemandeDestruction $demande): Response
    {
        if (!$this->isCsrfTokenValid('approve' . $demande->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_destruction_liste');
        }

        if ($demande->getStatut() !== 'EN_ATTENTE') {
            $this->addFlash('error', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('app_destruction_liste');
        }

        $demande->setStatut('APPROUVEE');
        $demande->setApprobateur($this->getUser());
        $demande->setDateTraitement(new \DateTime());

        // Générer le PDF d'autorisation
        try {
            $pdfPath = $this->pdfGenerator->generateAuthorizationPdf($demande);
            $demande->setFichierPreuve($pdfPath);
        } catch (\Exception $e) {
            $this->addFlash('warning', 'Demande approuvée mais erreur lors de la génération du PDF: ' . $e->getMessage());
        }

        $this->entityManager->flush();

        $this->audit->logApprove('DemandeDestruction', $demande->getId(), [
            'type_entite' => $demande->getTypeEntite(),
            'entite_id' => $demande->getEntiteId(),
            'libelle' => $demande->getLibelleEntite(),
        ]);

        $this->addFlash('success', 'Demande de destruction approuvée avec succès.');
        return $this->redirectToRoute('app_destruction_liste');
    }

    #[Route('/admin/destruction/{id}/rejeter', name: 'app_destruction_rejeter', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function rejeter(Request $request, DemandeDestruction $demande): Response
    {
        if (!$this->isCsrfTokenValid('reject' . $demande->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_destruction_liste');
        }

        if ($demande->getStatut() !== 'EN_ATTENTE') {
            $this->addFlash('error', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('app_destruction_liste');
        }

        $motifRejet = $request->request->get('motif_rejet');
        if (!$motifRejet) {
            $this->addFlash('error', 'Le motif de rejet est obligatoire.');
            return $this->redirectToRoute('app_destruction_liste');
        }

        $demande->setStatut('REJETEE');
        $demande->setApprobateur($this->getUser());
        $demande->setDateTraitement(new \DateTime());
        $demande->setMotifRejet($motifRejet);

        $this->entityManager->flush();

        $this->audit->logReject('DemandeDestruction', $demande->getId(), [
            'type_entite' => $demande->getTypeEntite(),
            'entite_id' => $demande->getEntiteId(),
            'motif' => $motifRejet,
        ]);

        $this->addFlash('success', 'Demande de destruction rejetée.');
        return $this->redirectToRoute('app_destruction_liste');
    }

    #[Route('/admin/destruction/{id}/executer', name: 'app_destruction_executer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function executer(Request $request, DemandeDestruction $demande): Response
    {
        if (!$this->isCsrfTokenValid('execute' . $demande->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_destruction_liste');
        }

        if ($demande->getStatut() !== 'APPROUVEE') {
            $this->addFlash('error', 'Cette demande doit être approuvée avant exécution.');
            return $this->redirectToRoute('app_destruction_liste');
        }

        $demande->setDateExecution(new \DateTime());
        $this->entityManager->flush();

        $this->audit->log('execute', 'DemandeDestruction', $demande->getId(), [
            'type_entite' => $demande->getTypeEntite(),
            'entite_id' => $demande->getEntiteId(),
            'libelle' => $demande->getLibelleEntite(),
        ]);

        $this->addFlash('success', 'Destruction exécutée et enregistrée.');
        return $this->redirectToRoute('app_destruction_liste');
    }

    #[Route('/admin/destruction/export-csv', name: 'app_destruction_export_csv', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function exportCsv(): Response
    {
        $demandes = $this->demandeDestructionRepository->findWithFilters('APPROUVEE');

        $response = new StreamedResponse(function () use ($demandes) {
            $handle = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($handle, [
                'ID',
                'Type',
                'Entité ID',
                'Libellé',
                'Demandeur',
                'Approbateur',
                'Date demande',
                'Date traitement',
                'Date exécution',
                'Justification',
            ]);

            // Données
            foreach ($demandes as $demande) {
                fputcsv($handle, [
                    $demande->getId(),
                    $demande->getTypeEntite(),
                    $demande->getEntiteId(),
                    $demande->getLibelleEntite(),
                    $demande->getDemandeur()->getNom() . ' ' . $demande->getDemandeur()->getPrenom(),
                    $demande->getApprobateur() ? $demande->getApprobateur()->getNom() . ' ' . $demande->getApprobateur()->getPrenom() : '',
                    $demande->getDateDemande()->format('Y-m-d H:i:s'),
                    $demande->getDateTraitement() ? $demande->getDateTraitement()->format('Y-m-d H:i:s') : '',
                    $demande->getDateExecution() ? $demande->getDateExecution()->format('Y-m-d H:i:s') : '',
                    $demande->getJustification(),
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="destructions_' . date('Y-m-d') . '.csv"');

        return $response;
    }
}
