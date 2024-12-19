<?php

namespace App\Repository;

use App\Entity\Attribute;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class AttributeRepository
 *
 * Repository for attribute database entity
 *
 * @extends ServiceEntityRepository<Attribute>
 *
 * @package App\Repository
 */
class AttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attribute::class);
    }
}
