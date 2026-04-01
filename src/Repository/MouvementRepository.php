<?php

namespace App\Repository;

use App\Entity\Mouvement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mouvement>
 *
 * @method Mouvement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mouvement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mouvement[]    findAll()
 * @method Mouvement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MouvementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mouvement::class);
    }

    /**
     * Trouve les mouvements récents (derniers 30 jours)
     */
    public function findRecent(int $limit = 50): array
    {
        $date = new \DateTime('-30 days');
        
        return $this->createQueryBuilder('m')
            ->where('m.date_mouvement >= :date')
            ->setParameter('date', $date)
            ->orderBy('m.date_mouvement', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les prêts en cours
     */
    public function findPretsEnCours(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.type_mouvement = :type')
            ->andWhere('m.statut = :statut')
            ->setParameter('type', 'pret')
            ->setParameter('statut', 'en_cours')
            ->orderBy('m.date_retour_prevue', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les prêts en retard
     */
    public function findPretsEnRetard(): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('m')
            ->where('m.type_mouvement = :type')
            ->andWhere('m.statut = :statut')
            ->andWhere('m.date_retour_prevue < :now')
            ->setParameter('type', 'pret')
            ->setParameter('statut', 'en_cours')
            ->setParameter('now', $now)
            ->orderBy('m.date_retour_prevue', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les mouvements par type
     */
    public function findByType(string $type, int $limit = 100): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.type_mouvement = :type')
            ->setParameter('type', $type)
            ->orderBy('m.date_mouvement', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les mouvements d'un fichier spécifique
     */
    public function findByFichier(int $fichierId): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.fichier = :fichier')
            ->setParameter('fichier', $fichierId)
            ->orderBy('m.date_mouvement', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les mouvements d'un dossier spécifique
     */
    public function findByDossier(int $dossierId): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.dossier = :dossier')
            ->setParameter('dossier', $dossierId)
            ->orderBy('m.date_mouvement', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des mouvements sur une période
     */
    public function getStatistiquesPeriode(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m.type_mouvement, COUNT(m.id) as nombre')
            ->where('m.date_mouvement BETWEEN :debut AND :fin')
            ->setParameter('debut', $dateDebut)
            ->setParameter('fin', $dateFin)
            ->groupBy('m.type_mouvement')
            ->getQuery();

        return $qb->getResult();
    }
}
