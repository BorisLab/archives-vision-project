<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\Utilisateur;
use App\Entity\StatutMessage;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function save(Message $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Message $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByConversation(Utilisateur $sender, Utilisateur $receiver): array
    {
    return $this->createQueryBuilder('m')
        ->where('(m.sender = :sender AND m.recipient = :recipient) OR (m.sender = :recipient AND m.recipient = :sender)')
        ->setParameter('sender', $sender)
        ->setParameter('recipient', $receiver)
        ->orderBy('m.date_creation', 'ASC')
        ->getQuery()
        ->getResult();
    }

    public function countUnreadMessages(Utilisateur $user): array
    {
        return $this->createQueryBuilder('m')
            ->select('IDENTITY(m.sender) as sender_id, COUNT(m.id) as unread_count')
            ->where('m.recipient = :user')
            ->andWhere('m.statut = :unread')
            ->groupBy('m.sender')
            ->setParameter('user', $user)
            ->setParameter('unread', StatutMessage::NON_LU)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Message[] Returns an array of Message objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Message
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
