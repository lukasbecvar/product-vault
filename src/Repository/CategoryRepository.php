<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class CategoryRepository
 *
 * Repository for category database entity
 *
 * @extends ServiceEntityRepository<Category>
 *
 * @package App\Repository
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Find categories list
     *
     * @return string[] List of categories
     */
    public function findCategoryNames(): array
    {
        // query for get names
        $queryBuilder = $this->createQueryBuilder('c')
            ->select('c.name')
            ->getQuery();

        // execute query
        $result = $queryBuilder->getResult();

        // build result array
        return array_map(fn($category) => $category['name'], $result);
    }

    /**
     * Remove unused categories
     *
     * @return int The number of removed categories
     */
    public function removeUnusedCategories(): int
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'DELETE FROM App\Entity\Category c
             WHERE c.id NOT IN (
                 SELECT DISTINCT IDENTITY(pc.category)
                 FROM App\Entity\ProductCategory pc
             )'
        );

        return $query->execute();
    }
}
