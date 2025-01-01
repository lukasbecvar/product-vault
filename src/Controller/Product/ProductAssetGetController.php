<?php

namespace App\Controller\Product;

use OpenApi\Attributes\Tag;
use App\Manager\ErrorManager;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Parameter;
use App\Manager\ProductAssetsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ProductAssetGetController
 *
 * Controller for getting product assets resources
 *
 * @package App\Controller\Product
 */
class ProductAssetGetController extends AbstractController
{
    private ErrorManager $errorManager;
    private ProductAssetsManager $productAssetsManager;

    public function __construct(ErrorManager $errorManager, ProductAssetsManager $productAssetsManager)
    {
        $this->errorManager = $errorManager;
        $this->productAssetsManager = $productAssetsManager;
    }

    /**
     * Get product icon
     *
     * @param Request $request Request object
     *
     * @return StreamedResponse Return product icon as streamed response
     */
    #[Tag(name: "Product")]
    #[Parameter(name: 'icon_file', in: 'query', description: 'Product icon file', example: 'testing-icon.png', required: true)]
    #[Response(response: JsonResponse::HTTP_NOT_FOUND, description: 'Product icon not found')]
    #[Response(response: StreamedResponse::HTTP_OK, description: 'The product icon')]
    #[Route('/api/product/asset/icon', methods:['GET'], name: 'get_product_icon')]
    public function getProductIcon(Request $request): StreamedResponse
    {
        // get icon file from request parameter
        $iconFile = $request->get('icon_file');

        // check if icon file set
        if ($iconFile == null) {
            $this->errorManager->handleError(
                message: 'Icon file not set',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // get product icon
        $productIcon = $this->productAssetsManager->getProductIcon($iconFile);

        // return product icon
        $response = new StreamedResponse(function () use ($productIcon) {
            echo $productIcon;
        });
        $response->headers->set('Content-Type', 'image/' . pathinfo($iconFile, PATHINFO_EXTENSION));
        $response->headers->set('Content-Disposition', 'inline; filename="' . basename($iconFile) . '"');
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Content-Length', (string) strlen($productIcon));
        return $response;
    }

    /**
     * Get product image
     *
     * @param Request $request Request object
     *
     * @return StreamedResponse Return product image as streamed response
     */
    #[Tag(name: "Product")]
    #[Parameter(name: 'image_file', in: 'query', description: 'Product image file', example: 'test-image-1.jpg', required: true)]
    #[Response(response: JsonResponse::HTTP_NOT_FOUND, description: 'Product image not found')]
    #[Response(response: StreamedResponse::HTTP_OK, description: 'The product image')]
    #[Route('/api/product/asset/image', methods:['GET'], name: 'get_product_image')]
    public function getProductImage(Request $request): StreamedResponse
    {
        // get image file from request parameter
        $imageFile = $request->get('image_file');

        // check if image file set
        if ($imageFile == null) {
            $this->errorManager->handleError(
                message: 'Image file not set',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // get product image
        $productImage = $this->productAssetsManager->getProductImage($imageFile);

        // return product image
        $response = new StreamedResponse(function () use ($productImage) {
            echo $productImage;
        });
        $response->headers->set('Content-Type', 'image/' . pathinfo($imageFile, PATHINFO_EXTENSION));
        $response->headers->set('Content-Disposition', 'inline; filename="' . basename($imageFile) . '"');
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Content-Length', (string) strlen($productImage));
        return $response;
    }
}
