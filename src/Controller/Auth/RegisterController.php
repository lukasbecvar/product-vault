<?php

namespace App\Controller\Auth;

use App\DTO\UserDTO;
use App\Util\AppUtil;
use App\Manager\UserManager;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
    #[OA\Post(
        summary: 'User registration action',
        description: 'Register a new user and return status',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'email', type: 'string', description: 'New user email', example: 'test@testing.test'),
                    new OA\Property(property: 'first-name', type: 'string', description: 'User first name', example: 'John'),
                    new OA\Property(property: 'last-name', type: 'string', description: 'User last name', example: 'Doe'),
                    new OA\Property(property: 'password', type: 'string', description: 'User password', example: 'securePassword123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'The success user register message'
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request data message'
            ),
            new OA\Response(
                response: 409,
                description: 'Email already exists error'
            )
        ]
    )]
    #[Route('/api/auth/register', methods:['POST'], name: 'auth_register')]
    public function register(Request $request): JsonResponse
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
        $userDTO->firstName = trim($data['first-name'] ?? '');
        $userDTO->lastName = trim($data['last-name'] ?? '');
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
