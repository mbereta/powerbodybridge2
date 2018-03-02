<?php

namespace Powerbody\Bridge\Service\Form;

use Powerbody\Bridge\Entity\Category\RepositoryInterface;
use Powerbody\Bridge\Model\ResourceModel\Imported\CategoryRepositoryInterface;

class CategoryDataImporter implements FormDataImporterInterface
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(
        RepositoryInterface $repository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->repository = $repository;
        $this->categoryRepository = $categoryRepository;
    }

    public function importFormData()
    {
        $categoryDataArray = $this->repository->findAll();
        foreach ($categoryDataArray as $categoryData) {
            $this->categoryRepository->addOrUpdateIfExist($categoryData->getData());
        }

        $categoryBaseIdsArray = array_map(function (\Magento\Framework\DataObject $categoryData) {
            return (int)$categoryData->getData('base_category_id');
        }, $categoryDataArray);

        $this->categoryRepository->deleteAllWithNotMatchingBaseId($categoryBaseIdsArray);
    }
}
