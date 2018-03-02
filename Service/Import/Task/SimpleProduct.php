<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Task;

use Powerbody\Bridge\Entity\Product\SimpleProductRepository;
use Powerbody\Bridge\Service\Import\Product\SimpleProductImporterInterface;
use Powerbody\Bridge\Service\Import\ProductDataComparator;
use Powerbody\Bridge\Model\ResourceModel\Imported\ManufacturerRepository as ImportedManufacturerRepository;
use Powerbody\Bridge\Model\ResourceModel\Imported\CategoryRepository as ImportedCategoryRepository;
use \Psr\Log\LoggerInterface as Logger;

class SimpleProduct implements TaskInterface
{
    protected $simpleProductRepository;

    protected $productDataComparator;

    protected $simpleProductImporter;

    protected $importedManufacturerRepository;

    protected $importedCategoryRepository;

    protected $logger;

    public function __construct(
        SimpleProductRepository $simpleProductRepository,
        ProductDataComparator $productDataComparator,
        SimpleProductImporterInterface $simpleProductImporter,
        ImportedManufacturerRepository $importedManufacturerRepository,
        ImportedCategoryRepository $importedCategoryRepository,
        Logger $logger
    ) {
        $this->simpleProductRepository = $simpleProductRepository;
        $this->productDataComparator = $productDataComparator;
        $this->simpleProductImporter = $simpleProductImporter;
        $this->importedManufacturerRepository = $importedManufacturerRepository;
        $this->importedCategoryRepository = $importedCategoryRepository;
        $this->logger = $logger;
    }

    public function run()
    {
        $this->logger->debug(__('Started simple product import:') . date('Y-m-d H:i:s', time()));
        $selectedManufacturerCollection = $this->importedManufacturerRepository
            ->getSelectedImportedManufacturerCollection();
        $selectedCategoryCollection = $this->importedCategoryRepository
            ->getSelectedImportedCategoryCollection();
        $selectedManufacturerIds = $this->importedManufacturerRepository
            ->createManufacturerBaseIdsArray($selectedManufacturerCollection);
        $selectedCategoryIds = $this->importedCategoryRepository
            ->createCategoryBaseIdsArray($selectedCategoryCollection);
        
        $productSkuArray = $this->simpleProductRepository->getProductSkuForCategoryAndManufacturer(
            $selectedManufacturerIds,
            $selectedCategoryIds
        );
        
        $this->simpleProductImporter->disableNotRequestedProducts($productSkuArray);
        
        if (true === empty($productSkuArray)) {
            return $this;
        }

        $productSkuArray = $this->productDataComparator->compareResponseDataWithExisting($productSkuArray);
        
        $productDataArray = $this->simpleProductRepository->getProductDataForSkuArray($productSkuArray);

        if (false === empty($productDataArray)) {
            $this->simpleProductImporter->processImport($productDataArray);
        }

        $this->logger->debug(__('Ended simple product import:') . date('Y-m-d H:i:s', time()));

        return $this;
    }
}
