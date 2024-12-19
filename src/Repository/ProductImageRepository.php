<?php

namespace App\Repository;

use App\Entity\ProductImage;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class ProductImageRepository
 *
 * Repository for product_image database entity
 *
 * @extends ServiceEntityRepository<ProductImage>
 *
 * @package App\Repository
 */
class ProductImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage::class);
    }
}
