<?php

namespace Powerbody\Bridge\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Powerbody\Bridge\Service\Form\CategoryDataImporter;
use Powerbody\Bridge\Service\Form\ManufacturerDataImporter;

class Index extends Action
{
    private $pageFactory;
    private $manufacturerDataImporter;
    private $categoryDataImporter;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param ManufacturerDataImporter $manufacturerDataImporter
     * @param CategoryDataImporter $categoryDataImporter
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ManufacturerDataImporter $manufacturerDataImporter,
        CategoryDataImporter $categoryDataImporter
    ) {
        $this->pageFactory = $pageFactory;
        $this->manufacturerDataImporter = $manufacturerDataImporter;
        $this->categoryDataImporter = $categoryDataImporter;
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}
