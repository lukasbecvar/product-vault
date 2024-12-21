<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class ProductRepository
 *
 * Repository for product database entity
 *
 * @extends ServiceEntityRepository<Product>
 *
 * @package App\Repository
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Find products by categories list
     *
     * @param array<string> $categoryNames Single category name or array of category names
     *
     * @return Product[] List of products
     */
    public function findByCategories(array $categoryNames): array
    {
        $categoryNames = (array) $categoryNames;

        // query for get products by categories
        return $this->createQueryBuilder('p')
            ->innerJoin('p.product_categories', 'pc')
            ->innerJoin('pc.category', 'c')
            ->where('c.name IN (:categoryNames)')
            ->setParameter('categoryNames', $categoryNames)
            ->groupBy('p.id')
            ->having('COUNT(DISTINCT c.id) = :categoryCount')
            ->setParameter('categoryCount', count($categoryNames))
            ->getQuery()
            ->getResult();
    }

    /**
     * Find products by attributes list
     *
     * @param array<string> $attributeNames Single attribute name or array of attribute names
     *
     * @return Product[] List of products
     */
    public function findByAttributes(array $attributeNames): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.product_attributes', 'pa')
            ->innerJoin('pa.attribute', 'a')
            ->where('a.name IN (:attributeNames)')
            ->setParameter('attributeNames', $attributeNames)
            ->groupBy('p.id')
            ->having('COUNT(DISTINCT a.name) = :attributeCount')
            ->setParameter('attributeCount', count($attributeNames))
            ->getQuery()
            ->getResult();
    }

    /**
     * Find products by attributes values
     *
     * @param array<string, string> $attributeValues Single attribute name and value or array of attribute names and values
     *
     * @return Product[] List of products
     */
    public function findByAttributesValues(array $attributeValues): array
    {
        // prepare query builder
        $queryBuilder = $this->createQueryBuilder('p')
            ->innerJoin('p.product_attributes', 'pa')
            ->innerJoin('pa.attribute', 'a')
            ->where('a.name IN (:attributeNames)')  // filter by attribute names
            ->setParameter('attributeNames', array_keys($attributeValues));

        // add condition for attribute values
        foreach ($attributeValues as $attributeName => $attributeValue) {
            $queryBuilder->andWhere('pa.value = :value_' . $attributeName)
                ->setParameter('value_' . $attributeName, $attributeValue);
        }

        // group by product to avoid duplicates
        $queryBuilder->groupBy('p.id');

        // make sure product has all the requested attributes
        $queryBuilder->having('COUNT(DISTINCT a.name) = :attributeCount')->setParameter(
            'attributeCount',
            count($attributeValues)
        );

        // execute query
        return $queryBuilder->getQuery()->getResult();
    }
}
