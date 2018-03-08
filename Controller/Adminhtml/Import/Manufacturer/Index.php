<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Controller\Adminhtml\Import\Manufacturer;

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
    private $manufacturerDataImporter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        FormDataImporterInterface $manufacturerDataImporter,
        LoggerInterface $logger
    ) {
        $this->manufacturerDataImporter = $manufacturerDataImporter;
        $this->pageFactory = $pageFactory;
        $this->logger = $logger;
        return parent::__construct($context);
    }

    public function execute() : Page
    {
        $this->_initGrid();

        /* @var $resultPage $resultPage */
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Import Manufacturers'));
        
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Powerbody_Bridge::import_manufacturers');
    }

    private function _initGrid()
    {
        try {
            $this->manufacturerDataImporter->importFormData();
        } catch (\Exception $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to synchronize manufacturers, data may be inconsistent.'));
        }
    }
}
