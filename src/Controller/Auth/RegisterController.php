<?php

namespace App\Controller\Auth;

use App\DTO\UserDTO;
use App\Util\AppUtil;
use App\Manager\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class RegisterController
 *
 * API controller for registering a new user
 *
 * @package App\Controller\Auth
 */
class RegisterController extends AbstractController
{
    private AppUtil $appUtil;
    private UserManager $userManager;
    private ValidatorInterface $validator;

    public function __construct(AppUtil $appUtil, UserManager $userManager, ValidatorInterface $validator)
    {
        $this->appUtil = $appUtil;
        $this->validator = $validator;
        $this->userManager = $userManager;
    }

    /**
     * Register a new user
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The JSON response
     */
    #[Route('/api/auth/register', methods:['POST'], name: 'auth_register')]
    public function index(Request $request): JsonResponse
    {
        // check if registration with API endpoint is enabled
        if (!$this->appUtil->isRegistrationWithApiEndpointEnabled()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Registration with API endpoint is disabled!',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // get data from request
        $data = json_decode($request->getContent(), true);

        // set data to DTO object
        $userDTO = new UserDTO();
        $userDTO->email = trim($data['email'] ?? '');
        $userDTO->firstName = trim($data['firstName'] ?? '');
        $userDTO->lastName = trim($data['lastName'] ?? '');
        $userDTO->password = trim($data['password'] ?? '');

        // validate data using DTO properties
        $violations = $this->validator->validate($userDTO);

        // build validation errors array (if any errors found)
        $errors = [];
        foreach ($violations as $violation) {
            /** @var ConstraintViolationInterface $violation */
            $errors[] = $violation->getMessage();
        }

        // return error response if any errors found
        if (count($errors) > 0) {
            return $this->json([
                'status' => 'error',
                'message' => implode(', ', $errors),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // register new user to database
        $this->userManager->registerUser(
            $userDTO->email,
            $userDTO->firstName,
            $userDTO->lastName,
            $userDTO->password
        );

        // return success response
        return $this->json([
            'status' => 'success',
            'message' => 'User: ' . $userDTO->email . ' created successfully!',
        ], JsonResponse::HTTP_CREATED);
    }
}
