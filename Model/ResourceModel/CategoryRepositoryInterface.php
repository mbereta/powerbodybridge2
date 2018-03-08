<?php

namespace Powerbody\Bridge\Model\ResourceModel;

use Powerbody\Bridge\Model\ResourceModel\Imported\Category as ImportedCategoryResourceModel;
use Powerbody\Bridge\Model\Imported\Category as ImportedCategoryModel;
use Powerbody\Bridge\Model\ResourceModel\Imported\Category\Collection as ImportedCategoryCollection;
use Magento\Catalog\Model\Category as CategoryModel;

interface CategoryRepositoryInterface
{
    /**
     * @param $categoryBaseId
     * @return ImportedCategoryModel
     */
    public function getImportedCategoryModelByBaseId($categoryBaseId);

    /**
     * @param array $activeCategoryIdsArray
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getNotSelectedImportedCategoryCollection(array $activeCategoryIdsArray);

    /**
     * @param array $selectedCategoryIdsArray
     * @return ImportedCategoryCollection
     */
    public function getSelectedCategoryCollectionByIds(array $selectedCategoryIdsArray);

    /**
     * @param $categoryId
     * @return CategoryModel
     */
    public function getCategoryModelById($categoryId);

    /**
     * @param CategoryModel $parentCategoryModel
     * @param int $rootCategoryId
     * @return int
     */
    public function getCatalogCategoryParentId(CategoryModel $parentCategoryModel,$rootCategoryId);

    /**
     * @param CategoryModel $categoryModel
     * @param int $rootCategoryId
     * @return string
     */
    public function getCatalogCategoryPath(CategoryModel $categoryModel, $rootCategoryId);
}
