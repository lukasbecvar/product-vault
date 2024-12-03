<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    #[Route('/', methods:['GET'], name: 'app_index')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'test'
        ]);
    }
}
