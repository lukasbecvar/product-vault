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

    /**
     * Find attributes list
     *
     * @return string[] List of attributes
     */
    public function findAttributeNames(): array
    {
        // query for get names
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('a.name')
            ->getQuery();

        // execute query
        $result = $queryBuilder->getResult();

        // build result array
        return array_map(fn($attribute) => $attribute['name'], $result);
    }

    /**
     * Remove unused attributes
     *
     * @return int The number of removed attributes
     */
    public function removeUnusedAttributes(): int
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'DELETE FROM App\Entity\Attribute a
             WHERE a.id NOT IN (
                 SELECT DISTINCT IDENTITY(pa.attribute)
                 FROM App\Entity\ProductAttribute pa
             )'
        );

        return $query->execute();
    }
}
