<?php

namespace Powerbody\Bridge\Model\ResourceModel;

use Powerbody\Bridge\Model\Imported\CategoryFactory as ImportedCategoryFactory;
use Powerbody\Bridge\Model\ResourceModel\Imported\Category as ImportedCategoryResourceModel;
use Powerbody\Bridge\Model\Imported\Category as ImportedCategoryModel;
use Powerbody\Bridge\Model\ResourceModel\Imported\Category\Collection as ImportedCategoryCollection;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var ImportedCategoryFactory
     */
    private $importedCategoryFactory;

    /**
     * @var ImportedCategoryResourceModel
     */
    private $importedCategoryResourceModel;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var ImportedCategoryCollection
     */
    private $importedCategoryCollection;

    /**
     * @var CategoryResourceModel
     */
    private $categoryResourceModel;

    public function __construct(
        ImportedCategoryFactory $importedCategoryFactory,
        ImportedCategoryResourceModel $importedCategoryResourceModel,
        ImportedCategoryCollection $importedCategoryCollection,
        CategoryFactory $categoryFactory,
        CategoryResourceModel $categoryResourceModel
    ) {
        $this->importedCategoryFactory = $importedCategoryFactory;
        $this->importedCategoryResourceModel = $importedCategoryResourceModel;
        $this->categoryFactory = $categoryFactory;
        $this->importedCategoryCollection = $importedCategoryCollection;
        $this->categoryResourceModel = $categoryResourceModel;
    }

    /**
     * @param int $categoryBaseId
     *
     * @return ImportedCategoryModel
     */
    public function getImportedCategoryModelByBaseId($categoryBaseId)
    {
        $importedCategoryModel = $this->importedCategoryFactory->create();
        $this->importedCategoryResourceModel->load(
            $importedCategoryModel,
            $categoryBaseId,
            'base_category_id'
        );

        return $importedCategoryModel;
    }

    /**
     * @param array $activeCategoryIdsArray
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getNotSelectedImportedCategoryCollection(array $activeCategoryIdsArray)
    {
        $notSelectedImportedCategoryCollection = $this->importedCategoryFactory
            ->create()
            ->getCollection();

        if (false === empty($activeCategoryIdsArray)) {
            $notSelectedImportedCategoryCollection
                ->addFieldToFilter('client_category_id', ['nin' => $activeCategoryIdsArray]);
        }

        return $notSelectedImportedCategoryCollection;
    }

    /**
     * @param array $selectedCategoryIdsArray
     *
     * @return ImportedCategoryCollection
     */
    public function getSelectedCategoryCollectionByIds(array $selectedCategoryIdsArray)
    {
        return $this->importedCategoryCollection
            ->addFieldToFilter('id', ['in' => $selectedCategoryIdsArray]);
    }

    /**
     * @param int $categoryId
     * @return CategoryModel
     */
    public function getCategoryModelById($categoryId)
    {
        $categoryModel = $this->categoryFactory->create();

        $this->categoryResourceModel->load(
            $categoryModel,
            $categoryId
        );

        return $categoryModel;
    }

    /**
     * @param CategoryModel $parentCategoryModel
     * @param int $rootCategoryId
     *
     * @return int
     */
    public function getCatalogCategoryParentId(
        CategoryModel $parentCategoryModel,
        $rootCategoryId
    ) {
        $parentId = $rootCategoryId;

        if ($parentCategoryModel && $parentCategoryModel->getId()) {
            $parentId = $parentCategoryModel->getId();
        }

        return $parentId;
    }

    /**
     * @param CategoryModel $categoryModel
     * @param int $rootCategoryId
     *
     * @return string
     */
    public function getCatalogCategoryPath(
        CategoryModel $categoryModel,
        $rootCategoryId
    ) {
        $categoryPath = $rootCategoryId;

        if ($categoryModel && $categoryModel->getId()) {
            $categoryPath = rtrim($categoryModel->getData('path'), '/');
        }

        return $categoryPath;
    }
}
