<?php

namespace App\Controller\Admin\Log;

use Exception;
use App\Manager\LogManager;
use OpenApi\Attributes\Tag;
use App\Manager\ErrorManager;
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
    private ErrorManager $errorManager;

    public function __construct(LogManager $logManager, ErrorManager $errorManager)
    {
        $this->logManager = $logManager;
        $this->errorManager = $errorManager;
    }

    /**
     * Update all logs status to READED
     *
     * @return JsonResponse The all logs update status response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (log manager)")]
    #[Route('/api/admin/log/status/set/all/readed', methods:['POST'], name: 'set_logs_status_all_readed')]
    #[Response(response: JsonResponse::HTTP_OK, description: 'Update logs status to READED successfully!')]
    #[Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: 'Error to update logs')]
    public function setLogsStatusAllReaded(): JsonResponse
    {
        try {
            $this->logManager->setAllLogsToReaded();
            return $this->json([
                'status' => 'success',
                'message' => 'Update all logs status to READED successfully!'
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to update all logs status to READED!',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage(),
            );
        }
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
