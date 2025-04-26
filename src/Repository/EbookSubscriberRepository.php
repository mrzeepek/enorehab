<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EbookSubscriber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EbookSubscriber>
 */
class EbookSubscriberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EbookSubscriber::class);
    }

    /**
     * Enregistre ou met à jour un abonné ebook
     * 
     * @param EbookSubscriber $subscriber
     * @param bool $flush Appliquer les changements immédiatement
     * @return void
     */
    public function save(EbookSubscriber $subscriber, bool $flush = true): void
    {
        $this->getEntityManager()->persist($subscriber);
        
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve un abonné par son adresse email
     * 
     * @param string $email
     * @return EbookSubscriber|null
     */
    public function findOneByEmail(string $email): ?EbookSubscriber
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Obtient les statistiques des abonnés d'ebook
     * 
     * @return array{total: int, today: int, mail_list: int}
     */
    public function getStats(): array
    {
        $total = $this->count([]);
        
        $today = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('DATE(e.download_date) = CURRENT_DATE()')
            ->getQuery()
            ->getSingleScalarResult();
            
        $mailList = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.mail_list = :mailList')
            ->setParameter('mailList', true)
            ->getQuery()
            ->getSingleScalarResult();
            
        return [
            'total' => (int) $total,
            'today' => (int) $today,
            'mail_list' => (int) $mailList
        ];
    }
}