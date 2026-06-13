<?php

namespace App\Repository;

use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Facture>
 */
class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    //    /**
    //     * @return Facture[] Returns an array of Facture objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Facture
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Récupère uniquement les factures liées à une année budgétaire précise
     */
    public function findFacturesByAnnee(int $annee): array
    {
        return $this->createQueryBuilder('f')        // 'f' représente la Facture
            ->join('f.budget', 'b')                  // On fait la liaison avec le Budget ('b')
            ->andWhere('b.annee = :annee')           // On filtre sur l'année du budget
            ->setParameter('annee', $annee)          // On injecte la valeur de l'année de façon sécurisée
            ->getQuery()
            ->getResult()
        ;
    }
}
