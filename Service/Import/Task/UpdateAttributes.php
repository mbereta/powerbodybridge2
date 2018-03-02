<?php

namespace Powerbody\Bridge\Service\Import\Task;

use \Psr\Log\LoggerInterface as Logger;

class UpdateAttributes implements TaskInterface
{
    /**
     * @var \Powerbody\Bridge\Service\Sync\Entity\Attribute
     */
    private $attribute;

    private $logger;

    /**
     * @param \Powerbody\Bridge\Service\Sync\Entity\Attribute $attribute
     */
    public function __construct(
        \Powerbody\Bridge\Service\Sync\Entity\Attribute $attribute,
        Logger $logger
    ) {
        $this->attribute = $attribute;
        $this->logger = $logger;
    }

    /**
     * Update product attributes
     *
     * @return void
     */
    public function run()
    {
        $this->logger->debug(__('Started attributes import:') . date('Y-m-d H:i:s', time()));

        $this->attribute->updateAttributes();

        $this->logger->debug(__('Started attributes import:') . date('Y-m-d H:i:s', time()));
    }
}
