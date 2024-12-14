<?php

namespace App\Controller\User;

use App\Manager\UserManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class UserInfoController
 *
 * API controller for getting user info
 *
 * @package App\Controller\User
 */
class UserInfoController extends AbstractController
{
    private UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Get user info
     *
     * @param Security $security The security object (for get user)
     *
     * @return JsonResponse The user info response
     */
    #[Route('/api/user/info', methods:['POST'], name: 'user_info')]
    public function userInfo(Security $security): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        // check if user exists
        if ($user === null) {
            return $this->json([
                'status' => 'error',
                'message' => 'user not found',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // get user id
        $id = $user->getId();

        // check if user id found
        if ($id === null) {
            return $this->json([
                'status' => 'error',
                'message' => 'user id not found',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // get user info
        $userInfo = $this->userManager->getUserInfo($id);

        // return user info
        return $this->json($userInfo, JsonResponse::HTTP_OK);
    }
}
