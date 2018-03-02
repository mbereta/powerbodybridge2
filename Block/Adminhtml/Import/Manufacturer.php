<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Block\Adminhtml\Import;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer\Collection;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer\CollectionFactory;

class Manufacturer extends Template
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(Context $context, CollectionFactory $collectionFactory, array $data = [])
    {    
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    public function getSaveFormAction() : string
    {
        return $this->getUrl('*/import_manufacturer/save');
    }

    public function getManufacturerCollection() : Collection
    {
        return $this->collectionFactory->create()->setOrder('name', 'asc');
    }
}
