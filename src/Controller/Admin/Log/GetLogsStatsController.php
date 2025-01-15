<?php

namespace App\Controller\Admin\Log;

use App\Manager\LogManager;
use OpenApi\Attributes\Tag;
use OpenApi\Attributes\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class GetLogsStatsController
 *
 * Controller for getting logs statistics and count
 *
 * @package App\Controller\Admin\Log
 */
class GetLogsStatsController extends AbstractController
{
    private LogManager $logManager;

    public function __construct(LogManager $logManager)
    {
        $this->logManager = $logManager;
    }

    /**
     * Get logs statistics and count
     *
     * @return JsonResponse The logs statistics and count
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (log manager)")]
    #[Response(response: JsonResponse::HTTP_OK, description: 'The logs statistics and count')]
    #[Route('/api/admin/logs/stats', methods:['GET'], name: 'get_logs_stats')]
    public function getLogsStats(): JsonResponse
    {
        // get logs stats
        $data = $this->logManager->getLogsStats();

        return $this->json([
            'status' => 'success',
            'data' => $data,
        ], JsonResponse::HTTP_OK);
    }
}
