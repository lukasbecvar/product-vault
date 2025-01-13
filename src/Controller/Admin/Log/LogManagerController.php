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
 * Class LogManagerController
 *
 * Controller for updating log status
 *
 * @package App\Controller\Admin\Log
 */
class LogManagerController extends AbstractController
{
    private LogManager $logManager;

    public function __construct(LogManager $logManager)
    {
        $this->logManager = $logManager;
    }

    /**
     * Update log status
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The log update status response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (log manager)")]
    #[Parameter(name: 'id', in: 'query', description: 'Log id', required: false)]
    #[Parameter(name: 'status', in: 'query', description: 'New log status', required: false)]
    #[Route('/api/admin/log/status/update', methods:['POST'], name: 'update_logs_status')]
    #[Response(response: JsonResponse::HTTP_OK, description: 'Log status updated successfully!')]
    #[Response(response: JsonResponse::HTTP_NOT_FOUND, description: 'Log not found!')]
    #[Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: 'Error to update log status!')]
    public function updateLogsStatus(Request $request): JsonResponse
    {
        // get request parameters
        $id = (int) $request->query->get('id');
        $status = (string) $request->query->get('status');

        // check if id is set
        if ($id == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Log id not set!'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if status is set
        if ($status == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'New log status not set!'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // update log status
        $this->logManager->updateLogStatus($id, $status);

        // return success message
        return $this->json([
            'status' => 'success',
            'message' => 'Log status updated successfully!'
        ], JsonResponse::HTTP_OK);
    }
}
