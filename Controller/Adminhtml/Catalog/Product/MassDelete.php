<?php

namespace Powerbody\Bridge\Controller\Adminhtml\Catalog\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class MassDelete extends \Magento\Catalog\Controller\Adminhtml\Product\MassDelete
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $productBuilder, $filter, $collectionFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productDeleted = 0;
        $importedProductsNotDeleted = 0;

        foreach ($collection->getItems() as $product) {
            try {
                $product->delete();
                $productDeleted++;
            } catch (\Powerbody\Bridge\Exception\ImportedProductDeleteException $e) {
                $importedProductsNotDeleted++;
            } catch (\Exception $ex) {
            }
        }
        
        $message = __('A total of %1 record(s) have been deleted.', $productDeleted);

        if ($importedProductsNotDeleted > 0) {
            $message .= __(' Unable to delete %1 imported product(s).', $importedProductsNotDeleted);
        }

        $this->messageManager->addSuccess($message);

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
    }
}
