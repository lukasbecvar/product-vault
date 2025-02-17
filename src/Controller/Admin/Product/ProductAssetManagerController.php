<?php

namespace App\Controller\Admin\Product;

use Exception;
use OpenApi\Attributes\Tag;
use App\Manager\ErrorManager;
use OpenApi\Attributes\Schema;
use App\Manager\ProductManager;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\JsonContent;
use App\Manager\ProductAssetsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ProductAssetManagerController
 *
 * Controller for managing product assets
 *
 * @package App\Controller\Admin\Product
 */
class ProductAssetManagerController extends AbstractController
{
    private ErrorManager $errorManager;
    private ProductManager $productManager;
    private ProductAssetsManager $productAssetsManager;

    public function __construct(
        ErrorManager $errorManager,
        ProductManager $productManager,
        ProductAssetsManager $productAssetsManager
    ) {
        $this->errorManager = $errorManager;
        $this->productManager = $productManager;
        $this->productAssetsManager = $productAssetsManager;
    }

    /**
     * Create product icon (update icon if product already has icon)
     *
     * @param Request $request Request object
     *
     * @return JsonResponse The status response in JSON
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (product manager)")]
    #[RequestBody(
        content: [
            new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(properties: [
                    new Property(
                        property: "product_id",
                        type: "integer",
                        description: "Product id to upload icon",
                        example: 1
                    ),
                    new Property(
                        property: "icon_file",
                        type: "string",
                        description: "Icon file to upload",
                        format: "binary"
                    )
                ])
            )
        ]
    )]
    #[Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Product not found",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "error"),
                new Property(property: "message", type: "string", example: "Product not found")
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_BAD_REQUEST,
        description: "Invalid request data",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "error"),
                new Property(property: "message", type: "string", example: "Invalid request data")
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_CREATED,
        description: "The icon file uploaded successfully",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "success"),
                new Property(property: "message", type: "string", example: "Icon file uploaded successfully")
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: "The icon file updated successfully",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "success"),
                new Property(property: "message", type: "string", example: "Icon file updated successfully")
            ]
        )
    )]
    #[Route('/api/admin/product/asset/icon/create', methods:['POST'], name: 'create_product_icon')]
    public function createProductIcon(Request $request): JsonResponse
    {
        // get request parameters
        $iconFile = $request->files->get('icon_file');
        $productId = (int) $request->request->get('product_id');

        // check if product id is set
        if ($productId == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if file is valid
        if ($iconFile == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Icon file not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!$iconFile->isValid()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid file.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $allowedMimeTypes = ['image/png', 'image/jpeg', 'image/gif'];
        if (!in_array($iconFile->getMimeType(), $allowedMimeTypes)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedMimeTypes)
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // get product by id
        $product = $this->productManager->getProductById($productId);

        // check if product found
        if ($product == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product: ' . $productId . ' not found.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // upload icon file
        try {
            if ($this->productAssetsManager->checkIfProductHaveIcon($product)) {
                $this->productAssetsManager->updateProductIcon($iconFile->getPathname(), $product, $iconFile->getClientOriginalExtension());
                return $this->json([
                    'status' => 'success',
                    'message' => 'Product icon updated successfully!',
                    'product_data' => $this->productManager->formatProductData($product)
                ], JsonResponse::HTTP_OK);
            } else {
                $this->productAssetsManager->createProductIcon($iconFile->getPathname(), $product, $iconFile->getClientOriginalExtension());
                return $this->json([
                    'status' => 'success',
                    'message' => 'Product icon uploaded successfully!',
                    'product_data' => $this->productManager->formatProductData($product)
                ], JsonResponse::HTTP_CREATED);
            }
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product icon upload failed',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }

    /**
     * Add image to product
     *
     * @param Request $request Request object
     *
     * @return JsonResponse The status response in JSON
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (product manager)")]
    #[RequestBody(
        content: [
            new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(properties: [
                    new Property(
                        property: "product_id",
                        type: "integer",
                        description: "Product id to upload image",
                        example: 1
                    ),
                    new Property(
                        property: "image_file",
                        type: "string",
                        description: "Image file to upload",
                        format: "binary"
                    )
                ])
            )
        ]
    )]
    #[Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Product not found",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "error"),
                new Property(property: "message", type: "string", example: "Product not found")
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_BAD_REQUEST,
        description: "Invalid request data",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "error"),
                new Property(property: "message", type: "string", example: "Invalid request data")
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_CREATED,
        description: "The image file uploaded successfully",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "success"),
                new Property(property: "message", type: "string", example: "Image file uploaded successfully")
            ]
        )
    )]
    #[Route('/api/admin/product/asset/create/image', methods:['POST'], name: 'create_product_image')]
    public function createProductImage(Request $request): JsonResponse
    {
        // get request parameters
        $iconFile = $request->files->get('image_file');
        $productId = (int) $request->request->get('product_id');

        // check if product id is set
        if ($productId == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if file is valid
        if ($iconFile == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Image file not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!$iconFile->isValid()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid file'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $allowedMimeTypes = ['image/png', 'image/jpeg', 'image/gif'];
        if (!in_array($iconFile->getMimeType(), $allowedMimeTypes)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedMimeTypes)
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // get product by id
        $product = $this->productManager->getProductById($productId);

        // check if product found
        if ($product == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product: ' . $productId . ' not found.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // upload image file
        try {
            $this->productAssetsManager->createProductImage($iconFile->getPathname(), $product, $iconFile->getClientOriginalExtension());
            return $this->json([
                'status' => 'success',
                'message' => 'Product image uploaded successfully!',
                'product_data' => $this->productManager->formatProductData($product)
            ], JsonResponse::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product image upload failed',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }

    /**
     * Delete product image
     *
     * @param Request $request Request object
     *
     * @return JsonResponse The status response in JSON
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (product manager)")]
    #[Parameter(name: 'product_id', in: 'query', description: 'Product id associated with image', required: true)]
    #[Parameter(name: 'image_file', in: 'query', description: 'Image file to delete', required: true)]
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: "The image deleted successfully",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "success"),
                new Property(property: "message", type: "string", example: "Image deleted successfully")
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Product or image not found",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "error"),
                new Property(property: "message", type: "string", example: "Product or image not found")
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: "The image delete failed",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "error"),
                new Property(property: "message", type: "string", example: "The image delete failed")
            ]
        )
    )]
    #[Route('/api/admin/product/asset/image/delete', methods:['DELETE'], name: 'delete_product_image')]
    public function deleteProductImage(Request $request): JsonResponse
    {
        // get request parameters
        $productId = (int) $request->request->get('product_id');
        $imageFile = (string) $request->request->get('image_file');

        // check if product id is set
        if ($productId == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if image id is set
        if ($imageFile == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Parameter "image_file" not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // get image id by file name
        $imageId = $this->productAssetsManager->getProductImageIdByFileName($imageFile);
        if ($imageId == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Image file: ' . $imageFile . ' not found.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // get product by id
        $product = $this->productManager->getProductById($productId);

        // check if product found
        if ($product == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product: ' . $productId . ' not found.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // check if product have image
        if (!$this->productAssetsManager->checkIfProductHaveImage($product, $imageId)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product: ' . $productId . ' does not have image: ' . $imageFile
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // delete product image
        try {
            $this->productAssetsManager->deleteProductImage($imageId);
            return $this->json([
                'status' => 'success',
                'message' => 'Product image: ' . $imageFile . ' deleted successfully',
                'product_data' => $this->productManager->formatProductData($product)
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product image delete failed',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }
}
