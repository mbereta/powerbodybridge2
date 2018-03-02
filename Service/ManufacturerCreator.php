<?php

namespace Powerbody\Bridge\Service;

use Powerbody\Bridge\Entity\Attribute\Repository as AttributeRepository;
use Powerbody\Bridge\Entity\Manufacturer\RepositoryInterface as ManufacturerEntityRepository;
use Powerbody\Bridge\Model\Imported\Manufacturer;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer as ImportedManufacturerResourceModel;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer\Collection as ImportedManufacturerCollection;
use Powerbody\Bridge\Model\ResourceModel\ManufacturerRepositoryInterface as ManufacturerResourceRepository;
use Powerbody\Manufacturer\Model\Manufacturer as ManufacturerModel;
use Powerbody\Manufacturer\Model\ManufacturerFactory as ManufacturerFactory;
use Powerbody\Manufacturer\Model\ResourceModel\Manufacturer as ManufacturerResourceModel;
use Powerbody\Manufacturer\Service\ConfigurationReaderInterface;

class ManufacturerCreator implements ManufacturerCreatorInterface
{
    /**
     * @var ManufacturerResourceModel
     */
    private $manufacturerResourceModel;
    
    /**
     * @var ManufacturerFactory
     */
    private $manufacturerFactory;
    
    /**
     * @var ImportedManufacturerResourceModel
     */
    private $importedManufacturerResourceModel;
    
    /**
     * @var ManufacturerEntityRepository
     */
    private $manufacturerEntityRepository;
    
    /**
     * @var ManufacturerResourceRepository
     */
    private $manufacturerResourceRepository;
    
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var ConfigurationReaderInterface
     */
    private $configurationReader;

    public function __construct(
        ManufacturerResourceModel $manufacturerResourceModel,
        ImportedManufacturerResourceModel $importedManufacturerResourceModel,
        ManufacturerFactory $manufacturerFactory,
        ManufacturerEntityRepository $manufacturerEntityRepository,
        ManufacturerResourceRepository $manufacturerResourceRepository,
        AttributeRepository $attributeRepositiory,
        ConfigurationReaderInterface $configurationReader
    ) {
        $this->manufacturerResourceModel = $manufacturerResourceModel;
        $this->manufacturerFactory = $manufacturerFactory;
        $this->importedManufacturerResourceModel = $importedManufacturerResourceModel;
        $this->manufacturerEntityRepository = $manufacturerEntityRepository;
        $this->manufacturerResourceRepository = $manufacturerResourceRepository;
        $this->attributeRepository = $attributeRepositiory;
        $this->configurationReader = $configurationReader;
    }
    
    public function addOrUpdateCatalogManufacturers()
    {
        $activeManufacturerIdsArray = [];
    
        $selectedImportedManufacturerCollection = $this->manufacturerResourceRepository
            ->getSelectedImportedManufacturerCollection();
    
        if ($selectedImportedManufacturerCollection->getSize() > 0) {
        
            $selectedManufacturerIdsArray = $this
                ->prepareManufacturerIdsArrayForWebservice($selectedImportedManufacturerCollection);
        
            $manufacturerDataArray = $this->manufacturerEntityRepository
                ->getSelectedManufacturerData($selectedManufacturerIdsArray);
        
            foreach ($manufacturerDataArray as $manufacturerData) {
                $manufacturerModel = $this->addOrUpdateSingleManufacturer($manufacturerData);
                $activeManufacturerIdsArray[] = $manufacturerModel->getData('id');
            }
        }
    
        $this->removeNotActiveManufacturers($activeManufacturerIdsArray);
    }
    
    /**
     * @param array $manufacturerData
     *
     * @return ManufacturerModel
     */
    public function addOrUpdateSingleManufacturer(array $manufacturerData)
    {
        $importedManufacturerModel = $this->manufacturerResourceRepository
            ->getImportedManufacturerModelByBaseId($manufacturerData['id']);
    
        $manufacturerData = $this->removeNotNeededDataFieldsFromArray($manufacturerData);
        
        $attributeOptionId = $this->attributeRepository
            ->getAttributeOptionIdByAttributeCodeAndValue('manufacturer', $manufacturerData['name']);
    
        if ('' !== $attributeOptionId) {
            $manufacturerData['attribute_option_id'] = $attributeOptionId;
        }
    
        if (null === $importedManufacturerModel->getData('client_manufacturer_id')) {
            $manufacturerModel = $this->createManufacturer($manufacturerData);
            $this->updateImportedManufacturer($importedManufacturerModel, $manufacturerModel);
        } else {
            $manufacturerModel = $this->updateManufacturer(
                $importedManufacturerModel,
                $manufacturerData
            );
        }
    
        return $manufacturerModel;
    }
    
    /**
     * @param array $manufacturerDataArray
     *
     * @return ManufacturerModel
     */
    public function createManufacturer(array $manufacturerDataArray)
    {
        $this->manufacturerResourceRepository
            ->downloadManufacturerLogo($manufacturerDataArray);
    
        $manufacturerModel = $this->manufacturerFactory->create();
        $manufacturerModel->setData($manufacturerDataArray);
        $manufacturerModel->addData([
            'created_at' => new \Datetime(),
            'margin' => $this->configurationReader->getMinimalMargin(),
        ]);
        $this->manufacturerResourceModel->save($manufacturerModel);
    
        return $manufacturerModel;
    }
    
    /**
     * @param Manufacturer $importedManufacturerModel
     * @param array $manufacturerDataArray
     *
     * @return ManufacturerModel
     */
    public function updateManufacturer(
        Manufacturer $importedManufacturerModel,
        array $manufacturerDataArray
    ) {
        $manufacturerModel = $this->manufacturerFactory->create();
        $this->manufacturerResourceModel->load(
            $manufacturerModel,
            $importedManufacturerModel->getData('client_manufacturer_id')
        );
    
        if ($manufacturerDataArray['logo'] !== $manufacturerModel->getData('logo')
            || $manufacturerDataArray['logo_normal'] !== $manufacturerModel->getData('logo_normal')
        ) {
            $manufacturerDataArray = $this->manufacturerResourceRepository
                ->downloadManufacturerLogo($manufacturerDataArray);
        }
    
        $manufacturerModel->addData($manufacturerDataArray);
        $this->manufacturerResourceModel->save($manufacturerModel);
    
        return $manufacturerModel;
    }
    
    /**
     * @param Manufacturer $importedManufacturerModel
     * @param ManufacturerModel $manufacturerModel
     */
    private function updateImportedManufacturer(
        Manufacturer $importedManufacturerModel,
        ManufacturerModel $manufacturerModel
    ) {
        $importedManufacturerModel->setData('client_manufacturer_id', $manufacturerModel->getId());
        $this->importedManufacturerResourceModel->save($importedManufacturerModel);
    }
    
    /**
     * @param ImportedManufacturerCollection $selectedImportedManufacturerCollection
     *
     * @return array
     */
    private function prepareManufacturerIdsArrayForWebservice(
        ImportedManufacturerCollection $selectedImportedManufacturerCollection
    ) {
        $manufacturerIdsArray = [];
    
        foreach ($selectedImportedManufacturerCollection as $selectedImportedManufacturerModel) {
            $manufacturerIdsArray[]['id'] = $selectedImportedManufacturerModel->getData('base_manufacturer_id');
        }
    
        return $manufacturerIdsArray;
    }
    
    /**
     * @param array $dataArray
     *
     * @return array
     */
    private function removeNotNeededDataFieldsFromArray(array $dataArray)
    {
        $unusedDataFields = ['store_id', 'id', 'related_manufacturer_ids'];
    
        return array_diff_key($dataArray, array_flip($unusedDataFields));
    }
    
    /**
     * @param array $activeManufacturerIdsArray
     */
    private function removeNotActiveManufacturers(array $activeManufacturerIdsArray)
    {
        $notSelectedImportedManufacturerCollection = $this->manufacturerResourceRepository
            ->getNotSelectedImportedManufacturerCollection($activeManufacturerIdsArray);
    
        foreach ($notSelectedImportedManufacturerCollection as $importedManufacturerModel) {
        
            $clientManufacturerId = $importedManufacturerModel->getData('client_manufacturer_id');
        
            if (null !== $clientManufacturerId) {
            
                $manufacturerModel = $this->manufacturerFactory->create();
                $this->manufacturerResourceModel->load(
                    $manufacturerModel,
                    $clientManufacturerId
                );
                $this->manufacturerResourceModel->delete($manufacturerModel);
            }
        
            $importedManufacturerModel->setData('client_manufacturer_id', null);
            $this->importedManufacturerResourceModel->save($importedManufacturerModel);
        }
    }
}
