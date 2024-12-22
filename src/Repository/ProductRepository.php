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
     * Find products by attributes values and optionally filter by categories
     *
     * @param array<string, string> $attributeValues Attribute name and value pairs
     * @param array<string>|null $categories List of category names (optional)
     *
     * @return Product[] List of products
     */
    public function findByAttributesValues(array $attributeValues, ?array $categories = null): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->innerJoin('p.product_attributes', 'pa')
            ->innerJoin('pa.attribute', 'a')
            ->where('a.name IN (:attributeNames)')
            ->setParameter('attributeNames', array_keys($attributeValues));

        // add attribute filtering conditions
        $orX = $queryBuilder->expr()->orX();
        foreach ($attributeValues as $attributeName => $attributeValue) {
            $orX->add($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('a.name', ':name_' . $attributeName),
                $queryBuilder->expr()->eq('pa.value', ':value_' . $attributeName)
            ));
            $queryBuilder->setParameter('name_' . $attributeName, $attributeName)
                ->setParameter('value_' . $attributeName, $attributeValue);
        }
        $queryBuilder->andWhere($orX);

        // optionally filter by categories
        if (!empty($categories)) {
            $queryBuilder->innerJoin('p.product_categories', 'pc')
                ->innerJoin('pc.category', 'c')
                ->andWhere('c.name IN (:categories)')
                ->setParameter('categories', $categories);
        }

        // group by product ID and ensure all attributes match
        $queryBuilder->groupBy('p.id')->having('COUNT(DISTINCT a.name) = :attributeCount')
            ->setParameter('attributeCount', count($attributeValues));

        return $queryBuilder->getQuery()->getResult();
    }
}
