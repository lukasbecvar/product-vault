<?php

namespace App\Controller\Admin\Log;

use App\Manager\LogManager;
use OpenApi\Attributes\Tag;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Parameter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class GetLogsController
 *
 * Controller for getting logs list
 *
 * @package App\Controller\Admin\Log
 */
class GetLogsController extends AbstractController
{
    private LogManager $logManager;

    public function __construct(LogManager $logManager)
    {
        $this->logManager = $logManager;
    }

    /**
     * Get logs list
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The logs list
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (log manager)")]
    #[Parameter(name: 'page', in: 'query', description: 'Page number', required: false)]
    #[Parameter(name: 'status', in: 'query', description: 'Status of the logs', required: false)]
    #[Response(response: JsonResponse::HTTP_OK, description: 'The logs list')]
    #[Response(response: JsonResponse::HTTP_NOT_FOUND, description: 'No logs found for specified filters')]
    #[Route('/api/admin/logs', methods:['GET'], name: 'get_logs_list')]
    public function getLogs(Request $request): JsonResponse
    {
        // get request parameters
        $page = (int) $request->query->get('page', '1');
        $status = (string) $request->query->get('status', 'UNREADED');

        // get logs list
        $logsData = $this->logManager->getFormatedLogs($status, $page);

        // check if logs list found
        if (empty($logsData['logs_data'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'No logs found for specified filters',
                'current_filters' => [
                    'status' => $status,
                    'page' => $page
                ]
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // return logs list
        return $this->json([
            'status' => 'success',
            'data' => $logsData
        ], JsonResponse::HTTP_OK);
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
