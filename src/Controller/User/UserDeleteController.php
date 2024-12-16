<?php

namespace App\Controller\User;

use App\Manager\UserManager;
use App\Manager\ErrorManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class UserDeleteController
 *
 * API controller for deleting user
 *
 * @package App\Controller\User
 */
class UserDeleteController extends AbstractController
{
    private UserManager $userManager;
    private ErrorManager $errorManager;

    public function __construct(UserManager $userManager, ErrorManager $errorManager)
    {
        $this->userManager = $userManager;
        $this->errorManager = $errorManager;
    }

    /**
     * Delete user
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The status response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/user/delete', methods:['POST'], name: 'user_delete')]
    public function updateUserPassword(Request $request): JsonResponse
    {
        // get request data
        $requestData = $request->toArray();
        $userId = $requestData['user-id'];

        // check if parameters are valid
        if (empty($userId)) {
            $this->errorManager->handleError(
                message: 'Parameter "status" are required!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // check if user id exist in database
        if (!$this->userManager->checkIfUserIdExistInDatabase($userId)) {
            $this->errorManager->handleError(
                message: 'User not found!',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // delete user
        $this->userManager->deleteUser($userId);

        // return success response
        return $this->json([
            'status' => 'success',
            'message' => 'User deleted successfully!',
        ], JsonResponse::HTTP_OK);
    }
}
