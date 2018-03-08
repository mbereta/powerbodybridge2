<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Model\ResourceModel\Imported;

use Powerbody\Bridge\Model\Imported\Manufacturer;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer\CollectionFactory;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer as ImportedManufacturerResourceModel;
use Powerbody\Bridge\Model\Imported\ManufacturerFactory as ImportedManufacturerFactory;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer\Collection as ImportedManufacturerCollection;

class ManufacturerRepository implements ManufacturerRepositoryInterface
{
    private $importedManufacturerResourceModel;

    private $importedManufacturerFactory;

    private $importedManufacturerCollectionFactory;

    public function __construct(
        CollectionFactory $importedManufacturerCollectionFactory,
        ImportedManufacturerResourceModel $importedManufacturerResourceModel,
        ImportedManufacturerFactory $importedManufacturerFactory
    ) {
        $this->importedManufacturerCollectionFactory = $importedManufacturerCollectionFactory;
        $this->importedManufacturerResourceModel = $importedManufacturerResourceModel;
        $this->importedManufacturerFactory = $importedManufacturerFactory;
    }

    public function addOrUpdateIfExist(array $manufacturerDataArray)
    {
        $manufacturer = $this->importedManufacturerFactory->create();
        $this->importedManufacturerResourceModel->load(
            $manufacturer,
            $manufacturerDataArray['base_manufacturer_id'],
            'base_manufacturer_id'
        );

        if (
            $manufacturer->getData('base_manufacturer_id') !== $manufacturerDataArray['base_manufacturer_id']
            || $manufacturer->getData('name') !== $manufacturerDataArray['name']
        ) {
            return $this->update($manufacturer, $manufacturerDataArray);
        }

        return $this->add($manufacturer);
    }

    public function deleteAllWithNotMatchingBaseId(array $baseIdsArray)
    {
        /** @var \Powerbody\Bridge\Model\ResourceModel\Imported\Category\Collection $collection */
        $collection = $this->importedManufacturerCollectionFactory->create();
        $collection->addFieldToFilter('base_manufacturer_id', ['nin' => $baseIdsArray]);
        $collection->walk('delete');
    }

    private function update(Manufacturer $manufacturer, array $manufacturerDataArray)
    {
        foreach ($manufacturerDataArray as $key => $value) {
            $manufacturer->setData($key, $value);
        }

        $this->importedManufacturerResourceModel->save($manufacturer);
    }

    private function add(Manufacturer $manufacturer)
    {
        $this->importedManufacturerResourceModel->save($manufacturer);
    }

    public function getSelectedImportedManufacturerCollection() : ImportedManufacturerCollection
    {
        /* @var $importedManufacturerCollection ImportedManufacturerCollection */
        $importedManufacturerCollection = $this->importedManufacturerFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('is_selected', 1);

        return $importedManufacturerCollection;
    }

    public function createManufacturerBaseIdsArray(ImportedManufacturerCollection $selectedManufacturerCollection) : array
    {
        return $selectedManufacturerCollection->getColumnValues('base_manufacturer_id');
    }

    public function getImportedManufacturerByBaseId($baseManufacturerId) : Manufacturer
    {
        $importedManufacturerModel = $this->importedManufacturerFactory->create();

        $this->importedManufacturerResourceModel->load(
            $importedManufacturerModel,
            $baseManufacturerId,
            'base_manufacturer_id'
        );

        return $importedManufacturerModel;
    }
}
