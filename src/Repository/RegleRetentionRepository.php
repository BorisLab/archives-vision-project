<?php

namespace App\Repository;

use App\Entity\RegleRetention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RegleRetention>
 */
class RegleRetentionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegleRetention::class);
    }

    /**
     * Trouver toutes les règles actives triées par libellé
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
