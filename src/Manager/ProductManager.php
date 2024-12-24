<?php

namespace App\Manager;

use DateTime;
use Exception;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductManager
 *
 * Manager for manipulating with products database
 *
 * @package App\Manager
 */
class ProductManager
{
    private LogManager $logManager;
    private ErrorManager $errorManager;
    private EntityManagerInterface $entityManager;

    public function __construct(
        LogManager $logManager,
        ErrorManager $errorManager,
        EntityManagerInterface $entityManager
    ) {
        $this->logManager = $logManager;
        $this->errorManager = $errorManager;
        $this->entityManager = $entityManager;
    }

    /**
     * Create new product
     *
     * @param string $name The product name
     * @param string $description The product description
     * @param string $price The product price
     * @param string $priceCurrency The product price currency (default: EUR)
     *
     * @return Product The created product entity object
     */
    public function createProduct(string $name, string $description, string $price, string $priceCurrency = 'EUR'): Product
    {
        // get current time
        $currentTime = new DateTime();

        // create new product entity
        $product = new Product();
        $product->setName($name);
        $product->setDescription($description);
        $product->setAddedTime($currentTime);
        $product->setLastEditTime($currentTime);
        $product->setPrice($price);
        $product->setPriceCurrency(strtoupper($priceCurrency));
        $product->setActive(true);

        // save product entity to database
        try {
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Product create error',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' created',
            level: LogManager::LEVEL_INFO,
        );

        return $product;
    }
}
