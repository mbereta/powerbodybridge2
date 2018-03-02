<?php

namespace Powerbody\Bridge\Model\ResourceModel;

use Powerbody\Bridge\Model\Imported\Manufacturer;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer\Collection as ImportedManufacturerCollection;

interface ManufacturerRepositoryInterface
{
    /**
     * @param int $manufacturerBaseId
     *
     * @return Manufacturer
     */
    public function getImportedManufacturerModelByBaseId($manufacturerBaseId);

    /**
     * @return string
     */
    public function getManufacturerDestinationUrl();

    /**
     * @return ImportedManufacturerCollection
     */
    public function getSelectedImportedManufacturerCollection();

    /**
     * @param array $activeManufacturerIdsArray
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getNotSelectedImportedManufacturerCollection(array $activeManufacturerIdsArray);

    /**
     * @param array $manufacturerDataArray
     *
     * @return array
     */
    public function downloadManufacturerLogo(array $manufacturerDataArray);
}
