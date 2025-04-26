<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Bilan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bilan>
 */
class BilanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bilan::class);
    }

    /**
     * Enregistre une demande de bilan en base de données
     * 
     * @param Bilan $bilan
     * @return void
     */
    public function save(Bilan $bilan): void
    {
        $this->getEntityManager()->persist($bilan);
        $this->getEntityManager()->flush();
    }

    /**
     * Récupère les demandes de bilan avec filtre optionnel sur le statut
     * 
     * @param string|null $status Statut de la demande (optionnel)
     * @param int $limit Nombre maximum de résultats
     * @return array<Bilan> Liste des demandes de bilan
     */
    public function findByStatus(?string $status = null, int $limit = 100): array
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->orderBy('b.submission_date', 'DESC')
            ->setMaxResults($limit);

        if ($status !== null) {
            $queryBuilder
                ->andWhere('b.status = :status')
                ->setParameter('status', $status);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Met à jour le statut d'une demande de bilan
     * 
     * @param int $id Identifiant de la demande
     * @param string $status Nouveau statut
     * @param string|null $notes Notes (optionnel)
     * @return bool Succès ou échec de la mise à jour
     */
    public function updateStatus(int $id, string $status, ?string $notes = null): bool
    {
        $bilan = $this->find($id);
        
        if (!$bilan) {
            return false;
        }

        $bilan->setStatus($status);
        
        if ($notes !== null) {
            $bilan->setNotes($notes);
        }

        $this->getEntityManager()->flush();
        
        return true;
    }
}