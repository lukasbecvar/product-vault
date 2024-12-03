<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ErrorController extends AbstractController
{
    #[Route('/error/notfound', methods:['GET'], name: 'error_not_found')]
    public function handleNotFoundError(): JsonResponse
    {
        return $this->json([
            'message' => 'Not Found',
        ], JsonResponse::HTTP_NOT_FOUND);
    }
}
