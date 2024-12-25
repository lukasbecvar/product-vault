<?php

namespace App\Manager;

use Exception;
use App\Entity\Attribute;
use App\Entity\ProductAttribute;
use App\Repository\AttributeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AttributeManager
 *
 * Manager for manipulating with product attributes
 *
 * @package App\Manager
 */
class AttributeManager
{
    private LogManager $logManager;
    private ErrorManager $errorManager;
    private EntityManagerInterface $entityManager;
    private AttributeRepository $attributeRepository;

    public function __construct(
        LogManager $logManager,
        ErrorManager $errorManager,
        EntityManagerInterface $entityManager,
        AttributeRepository $attributeRepository
    ) {
        $this->logManager = $logManager;
        $this->errorManager = $errorManager;
        $this->entityManager = $entityManager;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Check if attribute name already exists
     *
     * @param string $name The attribute name
     *
     * @return bool True if attribute name already exists, false otherwise
     */
    public function checkIfAttributeNameAlreadyExists(string $name): bool
    {
        return $this->attributeRepository->findOneBy(['name' => $name]) !== null;
    }

    /**
     * Get attribute entity object by id
     *
     * @param int $id The attribute id
     *
     * @return Attribute|null The attribute object or null if attribute not found
     */
    public function getAttributeById(int $id): ?Attribute
    {
        return $this->attributeRepository->find($id);
    }

    /**
     * Get attribute entity object by name
     *
     * @param string $name The attribute name
     *
     * @return Attribute|null The attribute object or null if attribute not found
     */
    public function getAttributeByName(string $name): ?Attribute
    {
        return $this->attributeRepository->findOneBy(['name' => $name]);
    }

    /**
     * Get attributes list
     *
     * @return string[] The attributes list
     */
    public function getAttributesList(): array
    {
        return $this->attributeRepository->findAttributeNames();
    }

    /**
     * Create attribute entity in database
     *
     * @param string $name The attribute name
     *
     * @return void
     */
    public function createAttribute(string $name): void
    {
        // check if attribute name already exists
        if ($this->checkIfAttributeNameAlreadyExists($name)) {
            $this->errorManager->handleError(
                message: 'Attribute name: ' . $name . ' already exists',
                code: JsonResponse::HTTP_CONFLICT
            );
        }

        // create attribute entity
        $attribute = new Attribute();
        $attribute->setName($name);

        try {
            // save attribute to database
            $this->entityManager->persist($attribute);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to create attribute',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Attribute created: ' . $name,
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Rename attribute
     *
     * @param int $id The attribute id
     * @param string $newName The new attribute name
     *
     * @return void
     */
    public function renameAttribute(int $id, string $newName): void
    {
        // check if attribute name already exists
        if ($this->checkIfAttributeNameAlreadyExists($newName)) {
            $this->errorManager->handleError(
                message: 'Attribute name: ' . $newName . ' already exists',
                code: JsonResponse::HTTP_CONFLICT
            );
        }

        // get attribute by id
        $attribute = $this->getAttributeById($id);

        // check if attribute found
        if (!$attribute) {
            $this->errorManager->handleError(
                message: 'Attribute not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // rename attribute
        $attribute->setName($newName);

        try {
            // save attribute to database
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to rename attribute',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Attribute renamed: ' . $newName,
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Delete attribute from database
     *
     * @param int $id The attribute id
     *
     * @return void
     */
    public function deleteAttribute(int $id): void
    {
        // get attribute by id
        $attribute = $this->getAttributeById($id);

        // check if attribute found
        if ($attribute === null) {
            $this->errorManager->handleError(
                message: 'Attribute not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // delete attribute
        try {
            $this->entityManager->remove($attribute);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to delete attribute',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Attribute deleted: ' . $attribute->getName(),
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Delete attributes by product id
     *
     * @param int $productId The product id
     *
     * @return void
     */
    public function deleteAttributesByProductId(int $productId): void
    {
        try {
            // delete related attributes
            $this->entityManager->createQueryBuilder()
                ->delete(ProductAttribute::class, 'pa')
                ->where('pa.product = :product_id')
                ->setParameter('product_id', $productId)
                ->getQuery()
                ->execute();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to delete attributes by product id: ' . $productId,
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
