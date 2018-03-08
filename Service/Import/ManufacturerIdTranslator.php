<?php

namespace Powerbody\Bridge\Service\Import;

use Powerbody\Bridge\Model\Imported\ManufacturerFactory as ImportedManufacturerFactory;

class ManufacturerIdTranslator implements IdTranslatorInterface
{
    private $importedManufacturerFactory;

    public function __construct(ImportedManufacturerFactory $manufacturerFactory)
    {
        $this->importedManufacturerFactory = $manufacturerFactory;
    }

    public function translateBaseToClientIds(array $baseManufacturerIdsArray) : array
    {
        $importedManufacturerCollection = $this->importedManufacturerFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('base_manufacturer_id', ['in' => $baseManufacturerIdsArray])
            ->addFieldToFilter('client_manufacturer_id', ['neq' => null]);

        $columnValues = $importedManufacturerCollection->getColumnValues('client_manufacturer_id');
        $columnValues = array_map('intval', $columnValues);

        return $columnValues;
    }
}
