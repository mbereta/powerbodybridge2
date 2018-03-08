<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Controller\Adminhtml\Import\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Powerbody\Bridge\Service\CategoryCreatorInterface;
use Powerbody\Bridge\Service\Imported\ImportedEntityServiceInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;

class Save extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var ImportedEntityServiceInterface
     */
    protected $importedCategoryService;

    /**
     * @var CategoryCreatorInterface
     */
    protected $categoryCreator;

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
        ImportedEntityServiceInterface $importedCategoryService,
        LoggerInterface $logger,
        CategoryCreatorInterface $categoryCreator,
        ResourceConnection $resourceConnection
    ) {
        $this->importedCategoryService = $importedCategoryService;
        $this->categoryCreator = $categoryCreator;
        $this->pageFactory = $pageFactory;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
        $this->dbConnection = $this->resourceConnection->getConnection();

        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            $this->dbConnection->beginTransaction();

            $selectedCategories = $this->getRequest()->getParam('category', []);
            $this->importedCategoryService->setAsSelectedOnlyIds($selectedCategories);
            $this->categoryCreator->addOrUpdateCatalogCategories($selectedCategories);
            $this->messageManager->addSuccessMessage(__('Your preferences have been updated.'));

            $this->dbConnection->commit();
        } catch (\Exception $e) {
            $this->dbConnection->rollBack();
            $this->logger->error($e);
            $this->messageManager->addErrorMessage(__('Failed to save your preferences, please try again.'));
        }

        return $this->_redirect('bridge/import_category/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Powerbody_Bridge::import_categories');
    }
}
