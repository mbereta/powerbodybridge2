<?php

namespace Powerbody\Bridge\Entity\Manufacturer;

interface RepositoryInterface
{
    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function findAll();

    /**
     * @param array $selectedManufacturerIdsArray
     * @return array
     */
    public function getSelectedManufacturerData(array $selectedManufacturerIdsArray);
}
