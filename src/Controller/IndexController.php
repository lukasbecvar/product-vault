<?php

namespace App\Controller;

use App\Entity\ProductImage;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes\Tag;
use OpenApi\Attributes\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class IndexController
 *
 * Main index controller for check api status
 *
 * @package App\Controller
 */
class IndexController extends AbstractController
{
    /**
     * Index route for check api status
     *
     * @return JsonResponse Return backend status as json response
     */
    #[Tag(name: "Index")]
    #[Response(response: JsonResponse::HTTP_OK, description: 'The api status')]
    #[Route('/', methods:['GET'], name: 'main_index')]
    public function index(): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'message' => 'product-vault is running!',
            'version' => $_ENV['APP_VERSION'],
        ], JsonResponse::HTTP_OK);
    }

    // #[Route('/test', methods:['GET'], name: 'test')]
    // public function test(ProductRepository $repo, EntityManagerInterface $em): JsonResponse
    // {
    //     $products = $repo->findAll();

    //     $test = $repo->find(1);

    //     $image = new ProductImage();
    //     $image->setImageFile('/storage/images/test-fewewfewefw-1.jpg');
    //     $image->setProduct($test);

    //     $em->persist($image);

    //     $em->flush();

    //     dd($products[3]->getProductAttributesRaw());
    // }
}
