<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Observer\Catalog\Product;

class ProtectSave implements \Magento\Framework\Event\ObserverInterface
{

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    private $dateTime;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->logger = $logger;
        $this->dateTime = $dateTime;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) : self
    {
        return $this;

        $product = $observer->getData('product');

        if (true === $product->getData('is_saving_by_import')) {
            return $this;
        }

        $isUpdatedOld = $product->getOrigData('is_updated_while_import');
        $isUpdatedNew = $product->getData('is_updated_while_import');

        if (true === boolval($isUpdatedNew)
            && boolval($isUpdatedOld) === boolval($isUpdatedNew)
        ) {
            throw new \Exception('This product was imported and can\'t be saved');
        }

        return $this;
    }

}
