<?php

namespace Powerbody\Bridge\Service\Form;

use Magento\Framework\DataObject;
use Powerbody\Bridge\Entity\Manufacturer\RepositoryInterface;
use Powerbody\Bridge\Model\ResourceModel\Imported\ManufacturerRepositoryInterface;

class ManufacturerDataImporter implements FormDataImporterInterface
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var ManufacturerRepositoryInterface
     */
    private $manufacturerRepository;

    public function __construct(
        RepositoryInterface $repository,
        ManufacturerRepositoryInterface $manufacturerRepository
    ) {
        $this->repository = $repository;
        $this->manufacturerRepository = $manufacturerRepository;
    }

    public function importFormData()
    {
        $manufacturerDataArray = $this->repository->findAll();
        foreach ($manufacturerDataArray as $manufacturerData) {
            $this->manufacturerRepository->addOrUpdateIfExist($manufacturerData->getData());
        }

        $manufacturerBaseIdsArray = array_map(function (DataObject $manufacturerData) {
            return (int)$manufacturerData->getData('base_manufacturer_id');
        }, $manufacturerDataArray);

        $this->manufacturerRepository->deleteAllWithNotMatchingBaseId($manufacturerBaseIdsArray);
    }
}
