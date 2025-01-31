<?php

namespace App\Controller\Admin\Log;

use App\Manager\LogManager;
use OpenApi\Attributes\Tag;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\JsonContent;
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
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: 'The logs list',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: 'status', type: 'string', example: 'success'),
                new Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new Property(
                            property: 'stats',
                            type: 'object',
                            properties: [
                                new Property(property: 'logs_count', type: 'integer', example: 101),
                                new Property(property: 'unreaded_logs_count', type: 'integer', example: 101),
                                new Property(property: 'readed_logs_count', type: 'integer', example: 0)
                            ]
                        ),
                        new Property(
                            property: 'logs_data',
                            type: 'array',
                            items: new Items(
                                type: 'object',
                                properties: [
                                    new Property(property: 'id', type: 'integer', example: 123),
                                    new Property(property: 'name', type: 'string', example: 'product-manager'),
                                    new Property(property: 'message', type: 'string', example: 'Product: Testing product with id: 1 deleted'),
                                    new Property(
                                        property: 'time',
                                        type: 'object',
                                        properties: [
                                            new Property(property: 'date', type: 'string', format: 'datetime', example: '2025-01-31 11:25:50.000000'),
                                            new Property(property: 'timezone_type', type: 'integer', example: 3),
                                            new Property(property: 'timezone', type: 'string', example: 'Europe/Prague')
                                        ]
                                    ),
                                    new Property(property: 'user_agent', type: 'string', example: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36'),
                                    new Property(property: 'request_uri', type: 'string', example: '/api/admin/product/delete'),
                                    new Property(property: 'request_method', type: 'string', example: 'DELETE'),
                                    new Property(property: 'ip_address', type: 'string', example: '172.19.0.1'),
                                    new Property(property: 'level', type: 'integer', example: 4),
                                    new Property(property: 'user_id', type: 'integer', example: 1),
                                    new Property(property: 'status', type: 'string', example: 'UNREADED')
                                ]
                            )
                        ),
                        new Property(
                            property: 'pagination_info',
                            type: 'object',
                            properties: [
                                new Property(property: 'total_logs_count', type: 'integer', example: 123),
                                new Property(property: 'current_page', type: 'integer', example: 1),
                                new Property(property: 'total_pages_count', type: 'integer', example: 3),
                                new Property(property: 'is_next_page_exists', type: 'boolean', example: true),
                                new Property(property: 'is_previous_page_exists', type: 'boolean', example: false),
                                new Property(property: 'last_page_number', type: 'integer', example: 3)
                            ]
                        )
                    ]
                )
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: 'No logs found for specified filters',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: 'status', type: 'string', example: 'error'),
                new Property(property: 'message', type: 'string', example: 'No logs found for specified filters'),
                new Property(
                    property: 'current_filters',
                    type: 'object',
                    properties: [
                        new Property(property: 'status', type: 'string', example: 'UNREADED'),
                        new Property(property: 'page', type: 'integer', example: 1)
                    ]
                )
            ]
        )
    )]
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
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: 'The logs statistics and count',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: 'status', type: 'string', example: 'success'),
                new Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new Property(property: 'logs_count', type: 'integer', example: 123),
                        new Property(property: 'unreaded_logs_count', type: 'integer', example: 123),
                        new Property(property: 'readed_logs_count', type: 'integer', example: 0)
                    ]
                )
            ]
        )
    )]
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
