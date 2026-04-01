<?php

namespace App\Controller;

use App\Entity\RegleRetention;
use App\Form\RegleRetentionType;
use App\Repository\RegleRetentionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Message;
use App\Entity\StatutNotification;
use App\Entity\StatutMessage;
use App\Entity\Notification;
use App\Entity\NiveauAccesNotification;

#[Route('/admin/retention')]
#[IsGranted('ROLE_ADMIN')]
class RetentionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RegleRetentionRepository $regleRetentionRepository
    ) {
    }

    #[Route('/regles', name: 'app_retention_regles', methods: ['GET'])]
    public function listeRegles(): Response
    {
        $regles = $this->regleRetentionRepository->findAllActive();

        return $this->render('retention/liste_regles.html.twig', [
            'regles' => $regles,
        ]);
    }

    #[Route('/regle/create', name: 'app_retention_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, Authorization $authorization): Response
    {
        $nbrNotifsUnread = $entityManager->getRepository(Notification::class)->count(['statut' => StatutNotification::NON_LU, 'niveau_acces' => NiveauAccesNotification::UTILISATEUR, 'utilisateur' => $this->getUser()->getId()]);
        $nbrMsgsUnread = $entityManager->getRepository(Message::class)->count(['statut' => StatutMessage::NON_LU, 'recipient' => $this->getUser()->getId()]);

        $regle = new RegleRetention();
        $form = $this->createForm(RegleRetentionType::class, $regle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($regle);
            $this->entityManager->flush();

            $this->addFlash('success', 'Règle de rétention créée avec succès.');
            return $this->redirectToRoute('app_retention_regles');
        }

        return $this->render('retention/form_regle.html.twig', [
            'nbr_notifs_unread' => $nbrNotifsUnread,
            'nbr_msgs_unread' => $nbrMsgsUnread,
            'form' => $form->createView(),
            'regle' => $regle,
            'is_edit' => false,
        ]);
    }

    #[Route('/regle/{id}/edit', name: 'app_retention_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RegleRetention $regle): Response
    {
        $form = $this->createForm(RegleRetentionType::class, $regle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Règle de rétention modifiée avec succès.');
            return $this->redirectToRoute('app_retention_regles');
        }

        return $this->render('retention/form_regle.html.twig', [
            'form' => $form->createView(),
            'regle' => $regle,
            'is_edit' => true,
        ]);
    }

    #[Route('/regle/{id}/delete', name: 'app_retention_delete', methods: ['POST'])]
    public function delete(Request $request, RegleRetention $regle): Response
    {
        if ($this->isCsrfTokenValid('delete' . $regle->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($regle);
            $this->entityManager->flush();

            $this->addFlash('success', 'Règle de rétention supprimée avec succès.');
        }

        return $this->redirectToRoute('app_retention_regles');
    }
}
