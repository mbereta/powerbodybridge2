<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Block\Adminhtml\Import;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use Powerbody\Bridge\Block\Adminhtml\Import\Category\RendererFactoryInterface;
use Powerbody\Bridge\Model\ResourceModel\Imported\Category\CollectionFactory;

class Category extends Template
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var RendererFactoryInterface
     */
    private $rendererFactory;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        RendererFactoryInterface $rendererFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->rendererFactory = $rendererFactory;
        parent::__construct($context, $data);
    }

    public function getSaveFormAction() : string
    {
        return $this->getUrl('*/import_category/save');
    }

    public function getCategoryTree() : array
    {
        return $this->collectionFactory->create()->setOrder('name', 'asc')->toTree();
    }

    public function renderNode(\Powerbody\Bridge\Model\Imported\Category $importedCategory) : string
    {
        return $this->rendererFactory->create($importedCategory)->toHtml();
    }
}
