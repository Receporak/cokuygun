<?php

namespace App\Repository;

use App\Entity\OrderState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderState>
 *
 * @method OrderState|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderState|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderState[]    findAll()
 * @method OrderState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderState::class);
    }

    public function add(OrderState $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OrderState $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
