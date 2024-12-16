<?php

namespace App\Controller\User;

use App\Manager\UserManager;
use App\Manager\ErrorManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class UserDataUpdateController
 *
 * API controller for updating user data
 *
 * @package App\Controller\User
 */
class UserDataUpdateController extends AbstractController
{
    private UserManager $userManager;
    private ErrorManager $errorManager;

    public function __construct(UserManager $userManager, ErrorManager $errorManager)
    {
        $this->userManager = $userManager;
        $this->errorManager = $errorManager;
    }

    /**
     * Update user password
     *
     * @param Security $security The security object (for get user)
     * @param Request $request The request object
     *
     * @return JsonResponse The update status response
     */
    #[Route('/api/user/data/update/password', methods:['PATCH'], name: 'user_data_update_password')]
    public function updateUserPassword(Security $security, Request $request): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        // check if user found
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'User not found!',
                code: JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        // get new password from request
        $data = $request->toArray();
        $newPassword = $data['new_password'] ?? null;

        // check if new password is set
        if ($newPassword === null || empty($newPassword)) {
            $this->errorManager->handleError(
                message: 'Parameter "new_password" is required!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // check if new password is valid
        if (strlen($newPassword) < 8 || strlen($newPassword) > 128) {
            $this->errorManager->handleError(
                message: 'Parameter "new_password" must be between 8 and 128 characters long!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // get user id
        $userId = $user->getId();

        // check if user id found
        if ($userId === null) {
            return $this->json([
                'status' => 'error',
                'message' => 'user id not found',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // update password
        $this->userManager->updateUserPassword($userId, $newPassword);

        // return success message
        return $this->json([
            'status' => 'success',
            'message' => 'Password updated successfully!'
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Update user role
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The update status response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/user/data/update/role', methods:['PATCH'], name: 'user_data_update_role')]
    public function updateUserRole(Request $request): JsonResponse
    {
        // get request data
        $requestData = $request->toArray();
        $userId = $requestData['user-id'] ?? null;
        $task = $requestData['task'] ?? null;
        $role = $requestData['role'] ?? null;

        // check if request data is valid
        if ($userId === null || $task === null || $role === null || empty($userId) || empty($task) || empty($role)) {
            $this->errorManager->handleError(
                message: 'Parameters: user-id, task(add, remove), role are required!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // check if user id is valid
        if (!is_numeric($userId)) {
            $this->errorManager->handleError(
                message: 'User id is not valid!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // check if task is valid
        if (!in_array($task, ['add', 'remove'])) {
            $this->errorManager->handleError(
                message: 'Task is not valid!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($task === 'add') {
            // add role to user
            $this->userManager->addRoleToUser((int) $userId, $role);

            // return success message
            return $this->json([
                'status' => 'success',
                'message' => 'Role added successfully!'
            ], JsonResponse::HTTP_OK);
        } elseif ($task === 'remove') {
            // remove role from user
            $this->userManager->removeRoleFromUser((int) $userId, $role);

            // return success message
            return $this->json([
                'status' => 'success',
                'message' => 'Role removed successfully!'
            ], JsonResponse::HTTP_OK);
        } else {
            $this->errorManager->handleError(
                message: 'Task is not valid!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Update user status
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The update status response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/user/data/update/status', methods: ['PATCH'], name: 'update_user_status')]
    public function updateUserStatus(Request $request): JsonResponse
    {
        // get request data
        $requestData = $request->toArray();
        $userId = $requestData['user-id'];
        $status = $requestData['status'];

        // check if parameters are valid
        if (empty($userId) || empty($status)) {
            $this->errorManager->handleError(
                message: 'Parameters user-id and status are required!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // check if user status already associated with user
        if ($this->userManager->getUserStatus($userId) === $status) {
            $this->errorManager->handleError(
                message: 'User status already set to: ' . $status,
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // update user status
        $this->userManager->updateUserStatus($userId, $status);

        // return success response
        return $this->json([
            'status' => 'success',
            'message' => 'User status updated successfully!',
        ], JsonResponse::HTTP_OK);
    }
}
