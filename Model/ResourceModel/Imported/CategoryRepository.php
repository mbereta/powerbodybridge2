<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Model\ResourceModel\Imported;

use Powerbody\Bridge\Model\Imported\Category;
use Powerbody\Bridge\Model\ResourceModel\Imported\Category\CollectionFactory;
use Powerbody\Bridge\Model\ResourceModel\Imported\Category as ImportedCategoryResourceModel;
use Powerbody\Bridge\Model\Imported\CategoryFactory as ImportedCategoryFactory;
use Powerbody\Bridge\Model\ResourceModel\Imported\Category\Collection as ImportedCategoryCollection;

class CategoryRepository implements CategoryRepositoryInterface
{
    private $importedCategoryResourceModel;

    private $importedCategoryFactory;

    private $importedCategoryCollectionFactory;

    public function __construct(
        CollectionFactory $importedCategoryCollectionFactory,
        ImportedCategoryResourceModel $importedCategoryResourceModel,
        ImportedCategoryFactory $importedCategoryFactory
    ) {
        $this->importedCategoryCollectionFactory = $importedCategoryCollectionFactory;
        $this->importedCategoryResourceModel = $importedCategoryResourceModel;
        $this->importedCategoryFactory = $importedCategoryFactory;
    }

    public function addOrUpdateIfExist(array $categoryDataArray)
    {
        $category = $this->importedCategoryFactory->create();
        $this->importedCategoryResourceModel->load(
            $category,
            $categoryDataArray['base_category_id'],
            'base_category_id'
        );

        if (
            $category->getData('base_category_id') !== $categoryDataArray['base_category_id']
            || $category->getData('name') !== $categoryDataArray['name']
            || $category->getData('parent_id') !== $categoryDataArray['parent_id']
            || $category->getData('path') !== $categoryDataArray['path']
        ) {
            return $this->update($category, $categoryDataArray);
        }
        return $this->add($category);
    }

    public function deleteAllWithNotMatchingBaseId(array $baseIdsArray)
    {
        /** @var \Powerbody\Bridge\Model\ResourceModel\Imported\Category\Collection $collection */
        $collection = $this->importedCategoryCollectionFactory->create();
        $collection->addFieldToFilter('base_category_id', ['nin' => $baseIdsArray]);
        $collection->walk('delete');
    }

    private function update(Category $category, array $categoryDataArray)
    {
        foreach ($categoryDataArray as $key => $value) {
            $category->setData($key, $value);
        }

        $this->importedCategoryResourceModel->save($category);
    }

    private function add(Category $category)
    {
        $this->importedCategoryResourceModel->save($category);
    }

    public function getSelectedImportedCategoryCollection() : ImportedCategoryCollection
    {
        /* @var $importedCategoryCollection ImportedCategoryCollection */
        $importedCategoryCollection = $this->importedCategoryFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('is_selected', 1);

        return $importedCategoryCollection;
    }

    public function createCategoryBaseIdsArray(ImportedCategoryCollection $selectedCategoryCollection) : array
    {
        return $selectedCategoryCollection->getColumnValues('base_category_id');
    }
}
