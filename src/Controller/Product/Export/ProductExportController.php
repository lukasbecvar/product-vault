<?php

namespace App\Controller\Product\Export;

use Exception;
use App\Util\ExportUtil;
use OpenApi\Attributes\Tag;
use App\Manager\ErrorManager;
use OpenApi\Attributes\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ProductExportController
 *
 * Controller for exporting products and downloading the exported data
 *
 * @package App\Controller\Product
 */
class ProductExportController extends AbstractController
{
    private ExportUtil $exportUtil;
    private ErrorManager $errorManager;

    public function __construct(ExportUtil $exportUtil, ErrorManager $errorManager)
    {
        $this->exportUtil = $exportUtil;
        $this->errorManager = $errorManager;
    }

    /**
     * Export products to json file
     *
     * @return StreamedResponse Return json file as streamed response
     */
    #[Tag(name: "Product export")]
    #[Response(response: StreamedResponse::HTTP_OK, description: 'The export data file download')]
    #[Route('/api/product/export/json', methods:['GET'], name: 'get_product_export_json')]
    public function getProductExportJson(): StreamedResponse
    {
        try {
            return $this->exportUtil->exportToJson();
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Export to export data',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? StreamedResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }

    /**
     * Export products to xlsx file
     *
     * @return StreamedResponse Return xlsx file as streamed response
     */
    #[Tag(name: "Product export")]
    #[Response(response: StreamedResponse::HTTP_OK, description: 'The export data file download')]
    #[Route('/api/product/export/xls', methods:['GET'], name: 'get_product_export_xlsx')]
    public function getProductExportXls(): StreamedResponse
    {
        try {
            return $this->exportUtil->exportToXls();
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Export to export data',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? StreamedResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }

    /**
     * Export products to xml file
     *
     * @return StreamedResponse Return xml file as streamed response
     */
    #[Tag(name: "Product export")]
    #[Response(response: StreamedResponse::HTTP_OK, description: 'The export data file download')]
    #[Route('/api/product/export/xml', methods:['GET'], name: 'get_product_export_xml')]
    public function getProductExportXml(): StreamedResponse
    {
        try {
            return $this->exportUtil->exportToXml();
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Export to export data',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? StreamedResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }
}
