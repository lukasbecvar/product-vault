<?php

namespace App\Tests\Repository;

use App\Entity\Category;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryRepositoryTest
 *
 * Test cases for doctrine category repository
 *
 * @package App\Tests\Repository
 */
class CategoryRepositoryTest extends KernelTestCase
{
    private EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @phpstan-ignore-next-line */
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Test find category names
     *
     * @return void
     */
    public function testFindCategoryNames(): void
    {
        /** @var \App\Repository\CategoryRepository $categoryRepository */
        $categoryRepository = $this->entityManager->getRepository(Category::class);

        // call test method
        $result = $categoryRepository->findCategoryNames();

        // assert result
        $this->assertIsString($result[0]);
    }
}
