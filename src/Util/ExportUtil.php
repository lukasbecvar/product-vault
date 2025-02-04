<?php

namespace App\Util;

use Exception;
use DOMDocument;
use SimpleXMLElement;
use App\Repository\ProductRepository;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class ExportUtil
 *
 * Util for data exporting
 *
 * @package App\Util
 */
class ExportUtil
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Export products to json file
     *
     * @return StreamedResponse Return json file as streamed response
     */
    public function exportToJson(): StreamedResponse
    {
        $response = new StreamedResponse(function () {
            $data = [];

            // get product list
            $products = $this->productRepository->findAll();

            foreach ($products as $product) {
                $categories = $product->getCategoriesRaw();
                foreach ($categories as $category) {
                    $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + 1;
                }

                // get time values
                $addedTime = $product->getAddedTime();
                $lastEditTime = $product->getLastEditTime();

                // format time values
                $addedTime = $addedTime ? $addedTime->format('Y-m-d H:i:s') : 'N/A';
                $lastEditTime = $lastEditTime ? $lastEditTime->format('Y-m-d H:i:s') : 'N/A';

                // add product data to array
                $data[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'priceCurrency' => $product->getPriceCurrency(),
                    'addedTime' => $addedTime,
                    'lastEditTime' => $lastEditTime,
                    'active' => $product->isActive(),
                    'categories' => implode(', ', $categories),
                    'attributes' => implode(', ', $product->getProductAttributesRaw()),
                    'product_icon' => $product->getIconFile(),
                    'product_images' => implode(', ', $product->getImagesRaw()),

                ];
            }

            // encode data to JSON and output directly
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });

        // return response
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment;filename="products-' . date('Y-m-d') . '.json"');
        return $response;
    }

    /**
     * Export products to xlsx file
     *
     * @return StreamedResponse Return xlsx file as streamed response
     */
    public function exportToXls(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // style table header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        // style table body
        $tableStyle = [
            'font' => ['color' => ['rgb' => 'EEEEEE']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '222222']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '555555']]],
        ];

        // set column widths
        $columns = [
            'A' => ['label' => 'ID', 'width' => 10],
            'B' => ['label' => 'Name', 'width' => 25],
            'C' => ['label' => 'Description', 'width' => 60],
            'D' => ['label' => 'Price', 'width' => 12],
            'E' => ['label' => 'Currency', 'width' => 10],
            'F' => ['label' => 'Added Time', 'width' => 20],
            'G' => ['label' => 'Last Edit', 'width' => 20],
            'H' => ['label' => 'Active', 'width' => 10],
            'I' => ['label' => 'Categories', 'width' => 60],
            'J' => ['label' => 'Attributes', 'width' => 60],
        ];

        foreach ($columns as $col => $data) {
            $sheet->setCellValue("{$col}1", $data['label']);
            $sheet->getColumnDimension($col)->setWidth($data['width']);
        }

        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        // get product list data
        $products = $this->productRepository->findAll();

        $row = 2;
        $categoryCounts = [];

        foreach ($products as $product) {
            $categories = $product->getCategoriesRaw();
            foreach ($categories as $category) {
                $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + 1;
            }

            // get time values
            $addedTime = $product->getAddedTime();
            $lastEditTime = $product->getLastEditTime();

            // format time values
            if ($addedTime != null) {
                $addedTime = $addedTime->format('Y-m-d H:i:s');
            } else {
                $addedTime = 'N/A';
            }
            if ($lastEditTime != null) {
                $lastEditTime = $lastEditTime->format('Y-m-d H:i:s');
            } else {
                $lastEditTime = 'N/A';
            }

            // set values
            $sheet->setCellValue("A$row", $product->getId());
            $sheet->setCellValue("B$row", $product->getName());
            $sheet->setCellValue("C$row", $product->getDescription());
            $sheet->setCellValue("D$row", $product->getPrice());
            $sheet->setCellValue("E$row", $product->getPriceCurrency());
            $sheet->setCellValue("F$row", $addedTime);
            $sheet->setCellValue("G$row", $lastEditTime);
            $sheet->setCellValue("H$row", $product->isActive() ? 'Yes' : 'No');
            $sheet->setCellValue("I$row", implode(', ', $categories));
            $sheet->setCellValue("J$row", implode(', ', $product->getProductAttributesRaw()));

            // apply table style
            $sheet->getStyle("A$row:J$row")->applyFromArray($tableStyle);
            $sheet->getStyle("A$row:H$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I$row:J$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $row++;
        }

        // create response
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->setIncludeCharts(true);
            $writer->save('php://output');
        });

        // return response
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="products-' . date('Y-m-d') . '.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    /**
     * Export products to xml file
     *
     * @return StreamedResponse Return xml file as streamed response
     */
    public function exportToXml(): StreamedResponse
    {
        $response = new StreamedResponse(function () {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><products></products>');

            // get product list
            $products = $this->productRepository->findAll();

            foreach ($products as $product) {
                // get product data and validate
                $productId = $product->getId() ?? 'Unknown';
                $productName = $product->getName() ?? 'Unknown';
                $productDescription = $product->getDescription() ?? 'Unknown';
                $productPrice = $product->getPrice() ?? 'Unknown';
                $productPriceCurrency = $product->getPriceCurrency() ?? 'Unknown';
                $productAddedTime = $product->getAddedTime() ? $product->getAddedTime()->format('Y-m-d H:i:s') : 'N/A';
                $productLastEditTime = $product->getLastEditTime() ? $product->getLastEditTime()->format('Y-m-d H:i:s') : 'N/A';
                $productActive = $product->isActive() ?? false;
                $productCategories = $product->getCategoriesRaw() ?? [];
                $productAttributes = $product->getProductAttributesRaw() ?? [];
                $productIcon = $product->getIconFile() ?? 'NULL';
                $productImages = $product->getImagesRaw() ?? [];

                // add product data to xml
                $productXml = $xml->addChild('product');
                $productXml->addChild('id', (string) $productId);
                $productXml->addChild('name', htmlspecialchars($productName, ENT_XML1, 'UTF-8'));
                $productXml->addChild('description', htmlspecialchars($productDescription, ENT_XML1, 'UTF-8'));
                $productXml->addChild('price', $productPrice);
                $productXml->addChild('priceCurrency', $productPriceCurrency);
                $productXml->addChild('addedTime', $productAddedTime);
                $productXml->addChild('lastEditTime', $productLastEditTime);
                $productXml->addChild('active', $productActive ? 'true' : 'false');

                // add categories to xml
                $categoriesXml = $productXml->addChild('categories');
                foreach ($productCategories as $category) {
                    if ($category !== null) {
                        $categoriesXml->addChild('category', htmlspecialchars($category, ENT_XML1, 'UTF-8'));
                    }
                }

                // add attributes to xml
                $attributesXml = $productXml->addChild('attributes');
                foreach ($productAttributes as $attribute) {
                    if ($attribute !== null) {
                        $attributesXml->addChild('attribute', htmlspecialchars($attribute, ENT_XML1, 'UTF-8'));
                    }
                }

                // add product assets to xml
                $productXml->addChild('product_icon', $productIcon);
                $imagesXml = $productXml->addChild('product_images');
                foreach ($productImages as $image) {
                    $imagesXml->addChild('image', $image);
                }
            }

            // format xml output
            $dom = new DOMDocument("1.0", "UTF-8");
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $xmlFile = $xml->asXML();
            if ($xmlFile == false) {
                throw new Exception('Error to encode/format xml data!');
            }
            $dom->loadXML($xmlFile);

            // print xml data to output buffer
            echo $dom->saveXML();
        });

        // return response
        $response->headers->set('Content-Type', 'application/xml');
        $response->headers->set('Content-Disposition', 'attachment;filename="products-' . date('Y-m-d') . '.xml"');
        return $response;
    }
}
