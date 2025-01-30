<?php

namespace App\Controller;

use App\Util\AppUtil;
use OpenApi\Attributes\Tag;
use OpenApi\Attributes\Response;
use Nelmio\ApiDocBundle\Attribute\Security;
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
    private AppUtil $appUtil;

    public function __construct(AppUtil $appUtil)
    {
        $this->appUtil = $appUtil;
    }

    /**
     * Index route for check api status
     *
     * @return JsonResponse Return backend status as json response
     */
    #[Tag(name: "Index")]
    #[Security(name: null)]
    #[Response(response: JsonResponse::HTTP_OK, description: 'The api status')]
    #[Route('/', methods:['GET'], name: 'main_index')]
    public function index(): JsonResponse
    {
        // return backend status
        $responseData = [
            'status' => 'success',
            'message' => 'product-vault is running!',
            'version' => $_ENV['APP_VERSION'],
        ];

        // add warning if app is running in dev mode
        if ($this->appUtil->isDevMode()) {
            $responseData['warning'] = 'App is running in dev mode (you can find documentation at /api/doc)!';
        }

        // return backend status as json response
        return new JsonResponse(json_encode($responseData, JSON_UNESCAPED_SLASHES), JsonResponse::HTTP_OK, json: true);
    }
}
