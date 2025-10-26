<?php

namespace App\Repository;

use App\Entity\AuditLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuditLog>
 *
 * @method AuditLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuditLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuditLog[]    findAll()
 * @method AuditLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditLog::class);
    }

    /**
     * Find logs with filters
     *
     * @param array $filters ['action' => string, 'entity_type' => string, 'user_id' => int, 'date_from' => DateTime, 'date_to' => DateTime]
     * @param int $limit
     * @param int $offset
     * @return AuditLog[]
     */
    public function findWithFilters(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($filters['action'])) {
            $qb->andWhere('a.action = :action')
               ->setParameter('action', $filters['action']);
        }

        if (!empty($filters['entity_type'])) {
            $qb->andWhere('a.entity_type = :entity_type')
               ->setParameter('entity_type', $filters['entity_type']);
        }

        if (!empty($filters['user_id'])) {
            $qb->andWhere('a.user_id = :user_id')
               ->setParameter('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $qb->andWhere('a.created_at >= :date_from')
               ->setParameter('date_from', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $qb->andWhere('a.created_at <= :date_to')
               ->setParameter('date_to', $filters['date_to']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Count logs with filters
     *
     * @param array $filters
     * @return int
     */
    public function countWithFilters(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)');

        if (!empty($filters['action'])) {
            $qb->andWhere('a.action = :action')
               ->setParameter('action', $filters['action']);
        }

        if (!empty($filters['entity_type'])) {
            $qb->andWhere('a.entity_type = :entity_type')
               ->setParameter('entity_type', $filters['entity_type']);
        }

        if (!empty($filters['user_id'])) {
            $qb->andWhere('a.user_id = :user_id')
               ->setParameter('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $qb->andWhere('a.created_at >= :date_from')
               ->setParameter('date_from', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $qb->andWhere('a.created_at <= :date_to')
               ->setParameter('date_to', $filters['date_to']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get audit statistics by action
     *
     * @param \DateTimeInterface|null $from
     * @param \DateTimeInterface|null $to
     * @return array
     */
    public function getStatsByAction(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.action', 'COUNT(a.id) as count')
            ->groupBy('a.action')
            ->orderBy('count', 'DESC');

        if ($from) {
            $qb->andWhere('a.created_at >= :from')
               ->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('a.created_at <= :to')
               ->setParameter('to', $to);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get recent logs for a specific entity
     *
     * @param string $entityType
     * @param int $entityId
     * @param int $limit
     * @return AuditLog[]
     */
    public function findByEntity(string $entityType, int $entityId, int $limit = 20): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.entity_type = :entity_type')
            ->andWhere('a.entity_id = :entity_id')
            ->setParameter('entity_type', $entityType)
            ->setParameter('entity_id', $entityId)
            ->orderBy('a.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
