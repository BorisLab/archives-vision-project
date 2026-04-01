<?php

namespace App\Repository;

use App\Entity\BoitePhysique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BoitePhysique>
 *
 * @method BoitePhysique|null find($id, $lockMode = null, $lockVersion = null)
 * @method BoitePhysique|null findOneBy(array $criteria, array $orderBy = null)
 * @method BoitePhysique[]    findAll()
 * @method BoitePhysique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoitePhysiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoitePhysique::class);
    }

    /**
     * Trouve les boîtes disponibles (non pleines)
     */
    public function findBoitesDisponibles(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.statut = :statut')
            ->setParameter('statut', true)
            ->orderBy('b.code_boite', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les boîtes par localisation
     */
    public function findByLocalisation(string $localisation): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.localisation LIKE :localisation')
            ->setParameter('localisation', '%' . $localisation . '%')
            ->orderBy('b.code_boite', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total de fichiers dans toutes les boîtes
     */
    public function countTotalFichiers(): int
    {
        return $this->createQueryBuilder('b')
            ->select('COUNT(f.id)')
            ->leftJoin('b.fichiers', 'f')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les boîtes proches de leur capacité maximale (> 80%)
     */
    public function findBoitesPresquePleines(): array
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b')
            ->leftJoin('b.fichiers', 'f')
            ->where('b.capacite_max IS NOT NULL')
            ->groupBy('b.id')
            ->having('COUNT(f.id) >= (b.capacite_max * 0.8)')
            ->orderBy('b.code_boite', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
