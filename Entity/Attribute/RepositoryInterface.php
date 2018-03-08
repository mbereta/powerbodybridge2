<?php

namespace Powerbody\Bridge\Entity\Attribute;

interface RepositoryInterface
{
    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function getAttributes();
}
