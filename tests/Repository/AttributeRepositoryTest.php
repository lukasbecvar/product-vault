<?php

namespace App\Tests\Repository;

use App\Entity\Attribute;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AttributeRepositoryTest
 *
 * Test cases for doctrine attribute repository
 *
 * @package App\Tests\Repository
 */
class AttributeRepositoryTest extends KernelTestCase
{
    private EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @phpstan-ignore-next-line */
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Test find attribute names
     *
     * @return void
     */
    public function testFindAttributeNames(): void
    {
        /** @var \App\Repository\AttributeRepository $attributeRepository */
        $attributeRepository = $this->entityManager->getRepository(Attribute::class);

        // call test method
        $result = $attributeRepository->findAttributeNames();

        // assert result
        $this->assertIsString($result[0]);
    }
}
