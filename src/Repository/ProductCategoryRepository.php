<?php

namespace App\Repository;

use App\Entity\ProductCategory;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class ProductCategoryRepository
 *
 * Repository for product_category database entity
 *
 * @extends ServiceEntityRepository<ProductCategory>
 *
 * @package App\Repository
 */
class ProductCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCategory::class);
    }
}
