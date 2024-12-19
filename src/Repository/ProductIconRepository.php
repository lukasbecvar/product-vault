<?php

namespace App\Repository;

use App\Entity\ProductIcon;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class ProductIconRepository
 *
 * Repository for product_icon database entity
 *
 * @extends ServiceEntityRepository<ProductIcon>
 *
 * @package App\Repository
 */
class ProductIconRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductIcon::class);
    }
}
