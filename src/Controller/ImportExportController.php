<?php

namespace App\Controller;

use App\Repository\DossierRepository;
use App\Repository\FichierRepository;
use App\Service\ExportService;
use App\Service\ImportService;
use App\Service\AuditLogger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\Authorization;
use App\Entity\Message;
use App\Entity\StatutNotification;
use App\Entity\StatutMessage;
use App\Entity\Notification;
use App\Entity\NiveauAccesNotification;

#[Route('/archivist/import-export')]
#[IsGranted("ROLE_ARCHIVIST")]
class ImportExportController extends AbstractController
{
    private ExportService $exportService;
    private ImportService $importService;
    private AuditLogger $auditLogger;

    public function __construct(
        ExportService $exportService,
        ImportService $importService,
        AuditLogger $auditLogger
    ) {
        $this->exportService = $exportService;
        $this->importService = $importService;
        $this->auditLogger = $auditLogger;
    }

    /**
     * Page principale d'import/export
     */
    #[Route('/', name: 'app_import_export', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, Authorization $authorization): Response
    {
        $this->setArchivistMercureCookie($request, $authorization); 

        return $this->render('archivemanager/import_export/index.html.twig', [
        "nbr_notifs_unread" => $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::UTILISATEUR, 'utilisateur' => $this->getUser()->getId()]),
        "nbr_msgs_unread" => $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()])
]);
    }

    /**
     * Exporter les dossiers
     */
    #[Route('/export/dossiers/{format}', name: 'app_export_dossiers', methods: ['GET'])]
    public function exportDossiers(
        string $format,
        DossierRepository $dossierRepo,
        Request $request
    ): Response {
        // Valider le format
        if (!in_array($format, ['xlsx', 'csv'])) {
            $this->addFlash('error', 'Format d\'export invalide');
            return $this->redirectToRoute('app_import_export_index');
        }

        // Récupérer les filtres depuis la requête (optionnel)
        $departementId = $request->query->get('departement_id');
        $statut = $request->query->get('statut');

        // Construire la requête
        $qb = $dossierRepo->createQueryBuilder('d')
            ->orderBy('d.libelle_dossier', 'ASC');

        if ($departementId) {
            $qb->andWhere('d.departement = :dept')
               ->setParameter('dept', $departementId);
        }

        if ($statut !== null) {
            $qb->andWhere('d.statut = :statut')
               ->setParameter('statut', $statut);
        }

        $dossiers = $qb->getQuery()->getResult();

        // Générer l'export
        $filepath = $this->exportService->exportDossiers($dossiers, $format);

        // Audit log
        $this->auditLogger->log('Export', 'Dossiers', null, [
            'format' => $format,
            'count' => count($dossiers),
            'filters' => [
                'departement_id' => $departementId,
                'statut' => $statut,
            ],
        ]);

        // Télécharger le fichier
        $response = new BinaryFileResponse($filepath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($filepath)
        );

        // Supprimer le fichier après envoi
        $response->deleteFileAfterSend(true);

        return $response;
    }

    /**
     * Exporter les fichiers
     */
    #[Route('/export/fichiers/{format}', name: 'app_export_fichiers', methods: ['GET'])]
    public function exportFichiers(
        string $format,
        FichierRepository $fichierRepo,
        Request $request
    ): Response {
        // Valider le format
        if (!in_array($format, ['xlsx', 'csv'])) {
            $this->addFlash('error', 'Format d\'export invalide');
            return $this->redirectToRoute('app_import_export_index');
        }

        // Récupérer les filtres
        $dossierId = $request->query->get('dossier_id');
        $type = $request->query->get('type');
        $statut = $request->query->get('statut');

        // Construire la requête
        $qb = $fichierRepo->createQueryBuilder('f')
            ->orderBy('f.libelle_fichier', 'ASC');

        if ($dossierId) {
            $qb->andWhere('f.dossier = :dossier')
               ->setParameter('dossier', $dossierId);
        }

        if ($type) {
            $qb->andWhere('f.type = :type')
               ->setParameter('type', $type);
        }

        if ($statut !== null) {
            $qb->andWhere('f.statut = :statut')
               ->setParameter('statut', $statut);
        }

        $fichiers = $qb->getQuery()->getResult();

        // Générer l'export
        $filepath = $this->exportService->exportFichiers($fichiers, $format);

        // Audit log
        $this->auditLogger->log('Export', 'Fichiers', null, [
            'format' => $format,
            'count' => count($fichiers),
            'filters' => [
                'dossier_id' => $dossierId,
                'type' => $type,
                'statut' => $statut,
            ],
        ]);

        // Télécharger le fichier
        $response = new BinaryFileResponse($filepath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($filepath)
        );

        $response->deleteFileAfterSend(true);

        return $response;
    }

    /**
     * Télécharger le template d'import pour les dossiers
     */
    #[Route('/template/dossiers', name: 'app_template_dossiers', methods: ['GET'])]
    public function templateDossiers(): Response
    {
        $filepath = $this->exportService->generateDossiersTemplate();

        $response = new BinaryFileResponse($filepath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'template_import_dossiers.xlsx'
        );

        $response->deleteFileAfterSend(true);

        return $response;
    }

    /**
     * Télécharger le template d'import pour les fichiers
     */
    #[Route('/template/fichiers', name: 'app_template_fichiers', methods: ['GET'])]
    public function templateFichiers(): Response
    {
        $filepath = $this->exportService->generateFichiersTemplate();

        $response = new BinaryFileResponse($filepath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'template_import_fichiers.xlsx'
        );

        $response->deleteFileAfterSend(true);

        return $response;
    }

    /**
     * Importer des dossiers
     */
    #[Route('/import/dossiers', name: 'app_import_dossiers', methods: ['POST'])]
    public function importDossiers(Request $request): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('import_dossiers', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_import_export_index');
        }

        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            $this->addFlash('error', 'Aucun fichier sélectionné');
            return $this->redirectToRoute('app_import_export_index');
        }

        // Vérifier l'extension
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            $this->addFlash('error', 'Format de fichier invalide. Formats acceptés : xlsx, xls, csv');
            return $this->redirectToRoute('app_import_export_index');
        }

        // Déplacer le fichier temporairement
        $filename = 'import_dossiers_' . uniqid() . '.' . $extension;
        $filepath = sys_get_temp_dir() . '/' . $filename;
        $uploadedFile->move(sys_get_temp_dir(), $filename);

        // Importer
        $result = $this->importService->importDossiers($filepath, $this->getUser());

        // Supprimer le fichier temporaire
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // Audit log
        $this->auditLogger->log('Import', 'Dossiers', null, [
            'success_count' => $result['success'],
            'errors_count' => count($result['errors']),
            'warnings_count' => count($result['warnings']),
        ]);

        // Messages flash
        if ($result['success'] > 0) {
            $this->addFlash('success', "{$result['success']} dossier(s) importé(s) avec succès");
        }

        foreach ($result['errors'] as $error) {
            $this->addFlash('error', $error);
        }

        foreach ($result['warnings'] as $warning) {
            $this->addFlash('warning', $warning);
        }

        return $this->redirectToRoute('app_import_export_index');
    }

    /**
     * Importer des fichiers
     */
    #[Route('/import/fichiers', name: 'app_import_fichiers', methods: ['POST'])]
    public function importFichiers(Request $request): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('import_fichiers', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_import_export_index');
        }

        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            $this->addFlash('error', 'Aucun fichier sélectionné');
            return $this->redirectToRoute('app_import_export_index');
        }

        // Vérifier l'extension
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            $this->addFlash('error', 'Format de fichier invalide. Formats acceptés : xlsx, xls, csv');
            return $this->redirectToRoute('app_import_export_index');
        }

        // Déplacer le fichier temporairement
        $filename = 'import_fichiers_' . uniqid() . '.' . $extension;
        $filepath = sys_get_temp_dir() . '/' . $filename;
        $uploadedFile->move(sys_get_temp_dir(), $filename);

        // Importer
        $result = $this->importService->importFichiers($filepath, $this->getUser());

        // Supprimer le fichier temporaire
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // Audit log
        $this->auditLogger->log('Import', 'Fichiers', null, [
            'success_count' => $result['success'],
            'errors_count' => count($result['errors']),
            'warnings_count' => count($result['warnings']),
        ]);

        // Messages flash
        if ($result['success'] > 0) {
            $this->addFlash('success', "{$result['success']} fichier(s) importé(s) avec succès");
        }

        foreach ($result['errors'] as $error) {
            $this->addFlash('error', $error);
        }

        foreach ($result['warnings'] as $warning) {
            $this->addFlash('warning', $warning);
        }

        return $this->redirectToRoute('app_import_export_index');
    }

    private function setArchivistMercureCookie(Request $request, Authorization $authorization)
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $baseUrl = $this->getParameter('app.base_url');
        $authorization->setCookie($request, [
            "{$baseUrl}/archivists",
            "{$baseUrl}/users/{$user->getId()}",
            "{$baseUrl}/status"
        ]);
    }
}
