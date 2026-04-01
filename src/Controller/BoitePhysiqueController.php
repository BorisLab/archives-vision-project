<?php

namespace App\Controller;

use App\Entity\BoitePhysique;
use App\Form\BoitePhysiqueType;
use App\Repository\BoitePhysiqueRepository;
use App\Service\AuditLogger;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/archivist/boites-physiques')]
#[IsGranted("ROLE_ARCHIVIST")]
class BoitePhysiqueController extends AbstractController
{
    private AuditLogger $auditLogger;
    private PaginatorInterface $paginator;

    public function __construct(AuditLogger $auditLogger, PaginatorInterface $paginator)
    {
        $this->auditLogger = $auditLogger;
        $this->paginator = $paginator;
    }

    #[Route('/', name: 'app_boite_physique_index', methods: ['GET'])]
    public function index(Request $request, BoitePhysiqueRepository $repository): Response
    {
        $queryBuilder = $repository->createQueryBuilder('b')
            ->orderBy('b.code_boite', 'ASC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20 // Items per page
        );
        
        return $this->render('administrator/boites_physiques/index.html.twig', [
            'boites' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_boite_physique_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $boite = new BoitePhysique();
        $form = $this->createForm(BoitePhysiqueType::class, $boite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($boite);
            $entityManager->flush();

            $this->auditLogger->logCreate('BoitePhysique', $boite->getId(), [
                'code_boite' => $boite->getCodeBoite(),
                'libelle' => $boite->getLibelle(),
                'localisation' => $boite->getLocalisation(),
            ]);

            $this->addFlash('success', 'Boîte physique créée avec succès');
            return $this->redirectToRoute('app_boite_physique_index');
        }

        return $this->render('administrator/boites_physiques/new.html.twig', [
            'boite' => $boite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_boite_physique_show', methods: ['GET'])]
    public function show(BoitePhysique $boite): Response
    {
        return $this->render('administrator/boites_physiques/show.html.twig', [
            'boite' => $boite,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_boite_physique_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BoitePhysique $boite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BoitePhysiqueType::class, $boite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->auditLogger->logUpdate('BoitePhysique', $boite->getId(), [
                'code_boite' => $boite->getCodeBoite(),
                'libelle' => $boite->getLibelle(),
                'localisation' => $boite->getLocalisation(),
            ]);

            $this->addFlash('success', 'Boîte physique modifiée avec succès');
            return $this->redirectToRoute('app_boite_physique_index');
        }

        return $this->render('administrator/boites_physiques/edit.html.twig', [
            'boite' => $boite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_boite_physique_delete', methods: ['POST'])]
    public function delete(Request $request, BoitePhysique $boite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$boite->getId(), $request->request->get('_token'))) {
            if ($boite->getNombreFichiers() > 0) {
                $this->addFlash('error', 'Impossible de supprimer une boîte contenant des fichiers');
                return $this->redirectToRoute('app_boite_physique_index');
            }

            $this->auditLogger->logDelete('BoitePhysique', $boite->getId(), [
                'code_boite' => $boite->getCodeBoite(),
                'libelle' => $boite->getLibelle(),
            ]);

            $entityManager->remove($boite);
            $entityManager->flush();

            $this->addFlash('success', 'Boîte physique supprimée avec succès');
        }

        return $this->redirectToRoute('app_boite_physique_index');
    }
}
