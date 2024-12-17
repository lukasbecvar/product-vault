<?php

namespace App\Controller\User;

use OpenApi\Attributes\Tag;
use App\Manager\UserManager;
use OpenApi\Attributes\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class UserListController
 *
 * API controller for getting users list
 *
 * @package App\Controller\User
 */
class UserListController extends AbstractController
{
    private UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Get users list
     *
     * @return JsonResponse The users list
     */
    #[Tag(name: "User")]
    #[Response(response: 200, description: 'The users list')]
    #[Response(response: 401, description: 'The unauthorized message')]
    #[Response(response: 500, description: 'The error to get user list')]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/user/list', methods:['GET'], name: 'user_list')]
    public function updateUserPassword(): JsonResponse
    {
        // get users list
        $users = $this->userManager->getUsersList();

        // return users list
        return $this->json([
            'status' => 'success',
            'users' => $users,
        ], JsonResponse::HTTP_OK);
    }
}
