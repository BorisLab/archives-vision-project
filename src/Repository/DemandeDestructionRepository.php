<?php

namespace App\Repository;

use App\Entity\DemandeDestruction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DemandeDestructionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeDestruction::class);
    }

    /**
     * Récupère toutes les demandes avec filtres optionnels
     */
    public function findWithFilters(?string $statut = null, ?int $demandeurId = null): array
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.demandeur', 'demandeur')
            ->leftJoin('d.approbateur', 'approbateur')
            ->addSelect('demandeur', 'approbateur')
            ->orderBy('d.date_demande', 'DESC');

        if ($statut) {
            $qb->andWhere('d.statut = :statut')
               ->setParameter('statut', $statut);
        }

        if ($demandeurId) {
            $qb->andWhere('d.demandeur = :demandeur')
               ->setParameter('demandeur', $demandeurId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte les demandes par statut
     */
    public function countByStatut(): array
    {
        $results = $this->createQueryBuilder('d')
            ->select('d.statut, COUNT(d.id) as total')
            ->groupBy('d.statut')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $row) {
            $counts[$row['statut']] = $row['total'];
        }

        return $counts;
    }

    /**
     * Récupère les demandes en attente
     */
    public function findPending(): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.demandeur', 'demandeur')
            ->addSelect('demandeur')
            ->where('d.statut = :statut')
            ->setParameter('statut', 'EN_ATTENTE')
            ->orderBy('d.date_demande', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les demandes approuvées non exécutées
     */
    public function findApprovedNotExecuted(): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.demandeur', 'demandeur')
            ->leftJoin('d.approbateur', 'approbateur')
            ->addSelect('demandeur', 'approbateur')
            ->where('d.statut = :statut')
            ->andWhere('d.date_execution IS NULL')
            ->setParameter('statut', 'APPROUVEE')
            ->orderBy('d.date_traitement', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
