<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Controller\Adminhtml\Import\Manufacturer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Powerbody\Bridge\Service\Imported\ImportedEntityServiceInterface;
use Powerbody\Bridge\Service\ManufacturerCreatorInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class Save extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var ImportedEntityServiceInterface
     */
    private $importManufacturerService;

    /**
     * @var ManufacturerCreatorInterface
     */
    protected $manufacturerCreator;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $dbConnection;

    /**
     * @var LoggerInterface
     */
	private $logger;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ImportedEntityServiceInterface $importedManufacturerService,
        LoggerInterface $logger,
        ManufacturerCreatorInterface $manufacturerCreator,
        ResourceConnection $resourceConnection
    ) {
        $this->importManufacturerService = $importedManufacturerService;
        $this->pageFactory = $pageFactory;
        $this->manufacturerCreator = $manufacturerCreator;
        $this->resourceConnection = $resourceConnection;
        $this->dbConnection = $this->resourceConnection->getConnection();
        $this->logger = $logger;
        return parent::__construct($context);
    }

    /**
     * @return ResponseInterface
     */
    public function execute()
    {

        try {
            $this->dbConnection->beginTransaction();

            $selectedManufacturers = $this->getRequest()->getParam('manufacturer', []);
            $this->importManufacturerService->setAsSelectedOnlyIds($selectedManufacturers);
            $this->manufacturerCreator->addOrUpdateCatalogManufacturers($selectedManufacturers);
            $this->messageManager->addSuccessMessage(__('Your preferences have been updated.'));

            $this->dbConnection->commit();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage(), [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'previous' => $e->getPrevious(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->messageManager->addErrorMessage(__('Failed to save your preferences, please try again.'));
            $this->dbConnection->rollBack();
        }

        return $this->_redirect('bridge/import_manufacturer/index');
    }
}
