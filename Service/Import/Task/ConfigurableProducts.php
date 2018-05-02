<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Task;

use Magento\Indexer\Model\Indexer\CollectionFactory as IndexerCollectionFactory;
use Powerbody\Bridge\Entity\Product\ConfigurableProductRepository;
use Powerbody\Bridge\Model\ResourceModel\Imported\ManufacturerRepository as ImportedManufacturerRepository;
use Powerbody\Bridge\Model\ResourceModel\Imported\CategoryRepository as ImportedCategoryRepository;
use Powerbody\Bridge\Service\Import\Product\Configurable\ProductImporterInterface;
use Powerbody\Bridge\Service\Import\Product\SimpleProductImporterInterface;
use Powerbody\Bridge\Service\Import\ProductDataComparatorInterface;
use \Psr\Log\LoggerInterface as Logger;

class ConfigurableProducts implements TaskInterface
{
    private $configurableProductRepository;

    private $importedManufacturerRepository;

    private $importedCategoryRepository;

    private $comparator;

    private $simpleProductImporter;

    private $configurableProductImporter;

    private $indexerCollectionFactory;

    private $logger;

    public function __construct(
        ConfigurableProductRepository $configurableProductRepository,
        ImportedManufacturerRepository $importedManufacturerRepository,
        ImportedCategoryRepository $importedCategoryRepository,
        ProductDataComparatorInterface $comparator,
        SimpleProductImporterInterface $simpleProductImporter,
        ProductImporterInterface $configurableProductImporter,
        IndexerCollectionFactory $indexerCollectionFactory,
        Logger $logger
    ) {
        $this->configurableProductRepository = $configurableProductRepository;
        $this->importedManufacturerRepository = $importedManufacturerRepository;
        $this->importedCategoryRepository = $importedCategoryRepository;
        $this->comparator = $comparator;
        $this->simpleProductImporter = $simpleProductImporter;
        $this->configurableProductImporter = $configurableProductImporter;
        $this->indexerCollectionFactory = $indexerCollectionFactory;
        $this->logger = $logger;
    }

    public function run()
    {
        $selectedManufacturerIds = $this->importedManufacturerRepository
            ->getSelectedImportedManufacturerCollection()
            ->getColumnValues('base_manufacturer_id');

        $selectedCategoryIds = $this->importedCategoryRepository
            ->getSelectedImportedCategoryCollection()
            ->getColumnValues('base_category_id');

        $productSkuArray = $this->configurableProductRepository->getSkuArray(
            $selectedManufacturerIds,
            $selectedCategoryIds
        );

        if (empty($productSkuArray)) {
            return;
        }

        $this->configurableProductImporter->disableNotRequestedProducts($productSkuArray);

        $productSkuArray = $this->comparator->compareResponseDataWithExisting($productSkuArray);
        $productDataArray = $this->configurableProductRepository->findBySku($productSkuArray);

        if (empty($productDataArray)) {
            return;
        }

        $this->configurableProductImporter->processImport($productDataArray);

        /** @var \Magento\Indexer\Model\Indexer\Collection $indexerCollection */
        $indexerCollection = $this->indexerCollectionFactory->create();
        foreach ($indexerCollection as $indexer) {
            /** @var \Magento\Indexer\Model\Indexer $indexer */
            $indexer->reindexAll();
        }
    }
}
