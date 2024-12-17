<?php

namespace App\Controller\Auth;

use Exception;
use OpenApi\Attributes\Tag;
use App\Manager\AuthManager;
use App\Manager\ErrorManager;
use OpenApi\Attributes\Response;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class SecurityController
 *
 * The controller for user authentication and authorization system
 *
 * @package App\Controller\Auth
 */
class SecurityController extends AbstractController
{
    private AuthManager $authManager;
    private ErrorManager $errorManager;

    public function __construct(AuthManager $authManager, ErrorManager $errorManager)
    {
        $this->authManager = $authManager;
        $this->errorManager = $errorManager;
    }

    /**
     * Login user from system with auth token invaldation
     *
     * @param Request $request The request object
     * @param Security $security The security object (for get user)
     *
     * @return JsonResponse The logout status response
     */
    #[Tag(name: "Auth")]
    #[Response(response: 200, description: 'The logout successful message')]
    #[Response(response: 401, description: 'The JWT token Invalid message')]
    #[Response(response: 500, description: 'The logout error message')]
    #[Route('/api/auth/logout', methods:['POST'], name: 'auth_logout')]
    public function index(Request $request, Security $security): JsonResponse
    {
        // get auth token from request
        $authToken = $this->authManager->getAuthTokenFromRequest($request);

        // check if auth token set in request
        if ($authToken == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'JWT token not set in request',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        try {
            // invalidate token auth token
            $this->authManager->logout($authToken, $security);

            return $this->json([
                'status' => 'success',
                'message' => 'user successfully logged out',
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'logout process error',
                code: JsonResponse::HTTP_BAD_REQUEST,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
