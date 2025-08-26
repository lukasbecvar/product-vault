<?php

namespace App\Repository;

use App\Entity\Product;
use InvalidArgumentException;
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
     * Find products by multiple criteria: search, attribute values, categories, pagination, and sorting
     *
     * @param string|null $search Search text for name or description (optional)
     * @param array<string, string>|null $attributeValues Attribute name-value pairs (optional)
     * @param array<string>|null $categories List of category names (optional)
     * @param int $page Page number for pagination (1-based)
     * @param int $limit Number of products per page (default: 100)
     * @param string|null $sort Sort by field: 'name', 'price', or 'added_time' (default: null)
     *
     * @return Product[] List of products
     */
    public function findByFilterCriteria(
        ?string $search = null,
        ?array $attributeValues = null,
        ?array $categories = null,
        int $page = 1,
        int $limit = 100,
        ?string $sort = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('p');

        // add search condition
        if (!empty($search)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('p.name', ':search'),
                    $queryBuilder->expr()->like('p.description', ':search')
                )
            )->setParameter('search', '%' . $search . '%');
        }

        // add attribute filtering conditions
        if (!empty($attributeValues)) {
            $queryBuilder->innerJoin('p.product_attributes', 'pa')
                ->innerJoin('pa.attribute', 'a')
                ->andWhere('a.name IN (:attributeNames)')
                ->setParameter('attributeNames', array_keys($attributeValues));

            $orX = $queryBuilder->expr()->orX();
            foreach ($attributeValues as $attributeName => $attributeValue) {
                $orX->add($queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('a.name', ':name_' . $attributeName),
                    $queryBuilder->expr()->eq('pa.value', ':value_' . $attributeName)
                ));
                $queryBuilder->setParameter('name_' . $attributeName, $attributeName)
                    ->setParameter('value_' . $attributeName, $attributeValue);
            }
            $queryBuilder->andWhere($orX)
                ->groupBy('p.id')
                ->having('COUNT(DISTINCT a.name) = :attributeCount')
                ->setParameter('attributeCount', count($attributeValues));
        }

        // add category filtering conditions
        if (!empty($categories)) {
            $queryBuilder->innerJoin('p.product_categories', 'pc')
                ->innerJoin('pc.category', 'c')
                ->andWhere('c.name IN (:categories)')
                ->setParameter('categories', $categories);
        }

        // ensure only active products are returned
        $queryBuilder->andWhere('p.is_active = 1');

        // add sorting
        if (!empty($sort)) {
            $validSortFields = ['name', 'price', 'added_time'];
            if (in_array($sort, $validSortFields, true)) {
                $queryBuilder->orderBy('p.' . $sort, 'ASC');
            } else {
                throw new InvalidArgumentException('Invalid sort parameter. Allowed values: name, price, added_time.');
            }
        }

        // pagination
        $queryBuilder->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        // return final query result
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get pagination information for products by criteria
     *
     * @param string|null $search Search text for name or description (optional)
     * @param array<string, string>|null $attributeValues Attribute name-value pairs (optional)
     * @param array<string>|null $categories List of category names (optional)
     * @param int $page Page number for pagination (1-based)
     * @param int $limit Number of products per page (default: 100)
     *
     * @return array{
     *     total_pages: int,
     *     current_page_number: int,
     *     total_items: int,
     *     items_per_actual_page: int,
     *     last_page_number: int,
     *     is_next_page_exists: bool,
     *     is_previous_page_exists: bool
     * }
     */
    public function getPaginationInfo(?string $search = null, ?array $attributeValues = null, ?array $categories = null, int $page = 1, int $limit = 100): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.id) AS total_items');

        // add search condition
        if (!empty($search)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('p.name', ':search'),
                    $queryBuilder->expr()->like('p.description', ':search')
                )
            )->setParameter('search', '%' . $search . '%');
        }

        // add attribute filtering conditions
        if (!empty($attributeValues)) {
            $queryBuilder->innerJoin('p.product_attributes', 'pa')
                ->innerJoin('pa.attribute', 'a')
                ->andWhere('a.name IN (:attributeNames)')
                ->setParameter('attributeNames', array_keys($attributeValues));

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
        }

        // add category filtering conditions
        if (!empty($categories)) {
            $queryBuilder->innerJoin('p.product_categories', 'pc')
                ->innerJoin('pc.category', 'c')
                ->andWhere('c.name IN (:categories)')
                ->setParameter('categories', $categories);
        }

        // ensure only active products are counted
        $queryBuilder->andWhere('p.is_active = 1');

        // get the total number of items
        $totalItems = (int)$queryBuilder->getQuery()->getSingleScalarResult();

        // calculate pagination details
        $totalPages = (int)ceil($totalItems / $limit);
        $currentPage = max(1, min($page, $totalPages));
        $isNextPageExists = $currentPage < $totalPages;
        $isPreviousPageExists = $currentPage > 1;

        // calculate items on the current page
        $startIndex = ($currentPage - 1) * $limit;
        $endIndex = min($startIndex + $limit, $totalItems);
        $itemsOnCurrentPage = (int) max(0, $endIndex - $startIndex);

        return [
            'total_pages' => $totalPages,
            'current_page_number' => $currentPage,
            'total_items' => $totalItems,
            'items_per_actual_page' => $itemsOnCurrentPage,
            'last_page_number' => $totalPages,
            'is_next_page_exists' => $isNextPageExists,
            'is_previous_page_exists' => $isPreviousPageExists
        ];
    }

    /**
     * Find products by search criteria (name or description)
     *
     * @param string $search The search string
     *
     * @return Product[] The list of products
     */
    public function findBySearchCriteria(string $search): array
    {
        $queryBuilder = $this->createQueryBuilder('p');

        return $queryBuilder->where($queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('p.name', ':search'),
            $queryBuilder->expr()->like('p.description', ':search')
        ))->setParameter('search', '%' . $search . '%')->getQuery()->getResult();
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
            ->andWhere('p.is_active = 1')
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
            ->andWhere('p.is_active = 1')
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
            ->andWhere('p.is_active = 1')
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

    /**
     * Get statistics for products
     *
     * @return array<int> The product stats
     */
    public function getProductStats(): array
    {
        // get total products count
        $total = (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // get active products count
        $active = (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.is_active = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();

        // get inactive products count
        $inactive = (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.is_active = :inactive')
            ->setParameter('inactive', false)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive
        ];
    }
}
