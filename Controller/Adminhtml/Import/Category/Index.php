<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Controller\Adminhtml\Import\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Powerbody\Bridge\Service\Form\FormDataImporterInterface;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var FormDataImporterInterface
     */
    private $categoryDataImporter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        FormDataImporterInterface $categoryDataImporter,
        LoggerInterface $logger
    ) {
        $this->categoryDataImporter = $categoryDataImporter;
        $this->pageFactory = $pageFactory;
        $this->logger = $logger;
        return parent::__construct($context);
    }

    public function execute() : Page
    {
        $this->_initGrid();

        /* @var $resultPage $resultPage */
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Import Categories'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Powerbody_Bridge::import_categories');
    }

    private function _initGrid()
    {
        try {
            $this->categoryDataImporter->importFormData();
        } catch (\Exception $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to synchronize categories, data may be inconsistent.'));
        }
    }
}
