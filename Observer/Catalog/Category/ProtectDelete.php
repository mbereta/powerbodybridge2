<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Observer\Catalog\Category;

class ProtectDelete implements \Magento\Framework\Event\ObserverInterface
{
    
    /** @var \Psr\Log\LoggerInterface */
    private $logger;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer) : self
    {
        $category = $observer->getData('category');

        if (true === boolval($category->getData('is_imported'))
            && false === boolval($category->getData('import_delete'))
        ) {
            throw new \Exception('This category is imported and can\'t be deleted');
        }

        return $this;
    }
    
}
