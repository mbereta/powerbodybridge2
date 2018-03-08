<?php

namespace Powerbody\Bridge\Service;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Powerbody\Bridge\Model\ResourceModel\Imported\Category\Collection as ImportedCategoryCollection;
use Powerbody\Bridge\Model\ResourceModel\Imported\Category as ImportedCategoryResourceModel;
use Powerbody\Bridge\Model\Imported\Category as ImportedCategoryModel;
use Magento\Catalog\Model\Category as CategoryModel;
use Powerbody\Bridge\Entity\Category\RepositoryInterface as CategoryEntityRepositoryInterface;
use Powerbody\Bridge\Model\ResourceModel\CategoryRepositoryInterface as CategoryResourceRepositoryInterface;

class CategoryCreator implements CategoryCreatorInterface
{
    const ROOT_CATALOG_CATEGORY_ID = 1;

    /**
     * @var ImportedCategoryResourceModel
     */
    private $importedCategoryResourceModel;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryResourceModel
     */
    private $categoryResourceModel;

    /**
     * @var CategoryEntityRepositoryInterface
     */
    private $categoryEntityRepository;

    /**
     * @var CategoryResourceRepositoryInterface
     */
    private $categoryResourceRepository;

    public function __construct(
        ImportedCategoryResourceModel $importedCategoryResourceModel,
        CategoryFactory $categoryFactory,
        StoreManagerInterface $storeManager,
        CategoryResourceModel $categoryResourceModel,
        CategoryEntityRepositoryInterface $categoryEntityRepository,
        CategoryResourceRepositoryInterface $categoryResourceRepository
    ) {
        $this->importedCategoryResourceModel = $importedCategoryResourceModel;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->categoryEntityRepository = $categoryEntityRepository;
        $this->categoryResourceRepository = $categoryResourceRepository;
    }

    public function addOrUpdateCatalogCategories(array $selectedCategoriesIdsArray)
    {
        $activeCategoryIdsArray = [];
        $selectedImportedCategoryCollection = $this->categoryResourceRepository
            ->getSelectedCategoryCollectionByIds($selectedCategoriesIdsArray);

        if ($selectedImportedCategoryCollection->getSize() > 0) {

            $selectedCategoryIdsArray = $this
                ->prepareCategoryIdsArrayForWebservice($selectedImportedCategoryCollection);

            $categoriesDataArray = $this->categoryEntityRepository
                ->getSelectedCategoryData($selectedCategoryIdsArray);

            foreach ($categoriesDataArray as $categoryDataArray) {
                $categoryModel = $this->addOrUpdateSingleCategory($categoryDataArray);
                $activeCategoryIdsArray[] = $categoryModel->getData('entity_id');
            }
        }

        $this->removeNotActiveCategories($activeCategoryIdsArray);
    }

    /**
     * @param array $categoryDataArray
     *
     * @return CategoryModel
     */
    public function addOrUpdateSingleCategory(array $categoryDataArray)
    {
        $importedCategoryModel = $this->categoryResourceRepository
            ->getImportedCategoryModelByBaseId($categoryDataArray['entity_id']);
        $clientCategoryId = $importedCategoryModel->getData('client_category_id');

        $categoryDataArray = $this->removeNotNeededDataFieldsFromArray($categoryDataArray);

        if (null === $clientCategoryId) {
            $categoryModel = $this->createCategory($categoryDataArray);
            $this->updateImportedCategory($importedCategoryModel, $categoryModel);

        } else {
            $categoryModel = $this->categoryResourceRepository->getCategoryModelById($clientCategoryId);
            $categoryModel->setData('is_anchor', false);
            $categoryModel->save();
        }

        return $categoryModel;
    }

    /**
     * @param array $categoryDataArray
     *
     * @return CategoryModel
     */
    public function createCategory(array $categoryDataArray)
    {
        $store = $this->storeManager->getStore();
        $rootCategoryId = $store->getRootCategoryId();

        $importedCategoryModel = $this->categoryResourceRepository
            ->getImportedCategoryModelByBaseId($categoryDataArray['parent_id']);
        $parentCatalogCategoryId = $importedCategoryModel->getData('client_category_id');

        if (null === $parentCatalogCategoryId) {
            $parentCatalogCategoryId = self::ROOT_CATALOG_CATEGORY_ID;
        }

        $parentCatalogCategoryModel = $this->categoryResourceRepository
            ->getCategoryModelById($parentCatalogCategoryId);

        if (true === isset($categoryDataArray['children_count'])) {
            $categoryDataArray['children_count'] = 0;
        }

        if (true === isset($categoryDataArray['parent_id'])) {
            $categoryDataArray['parent_id'] = $this->categoryResourceRepository
                ->getCatalogCategoryParentId(
                    $parentCatalogCategoryModel,
                    $rootCategoryId
                );
        }

        if (true === isset($categoryDataArray['path'])) {
            $categoryDataArray['path'] = $this->categoryResourceRepository->getCatalogCategoryPath(
                $parentCatalogCategoryModel,
                $rootCategoryId
            );
        }

        $categoryDataArray['is_imported'] = true;
        $categoryDataArray['is_anchor'] = false;

        $catalogCategoryModel = $this->categoryFactory->create();
        $catalogCategoryModel->setData($categoryDataArray);
        $this->categoryResourceModel->save($catalogCategoryModel);

        return $catalogCategoryModel;
    }

    /**
     * @param ImportedCategoryModel $importedCategoryModel
     *
     * @param CategoryModel $categoryModel
     */
    private function updateImportedCategory(
        ImportedCategoryModel $importedCategoryModel,
        CategoryModel $categoryModel
    ) {
        $importedCategoryModel->setData('client_category_id', $categoryModel->getId());
        $this->importedCategoryResourceModel->save($importedCategoryModel);
    }

    /**
     * @param ImportedCategoryCollection $selectedImportedCategoryCollection
     *
     * @return array
     */
    private function prepareCategoryIdsArrayForWebservice(
        ImportedCategoryCollection $selectedImportedCategoryCollection
    ) {
        $categoryIdsArray = [];
        $pathArray = [];

        foreach($selectedImportedCategoryCollection as $selectedImportedCategoryModel) {
            $categoryIdsArray[]['id'] = $selectedImportedCategoryModel->getData('base_category_id');
            $pathCategories = explode('/', $selectedImportedCategoryModel->getData('path'));
            $pathArray = array_merge($pathArray, $pathCategories);
        }

        foreach ($pathArray as $path) {
            $categoryIdsArray[]['id'] = $path;
        }

        return $categoryIdsArray;
    }

    /**
     * @param array $activeCategoryIdsArray
     */
    private function removeNotActiveCategories(array $activeCategoryIdsArray)
    {
        $notSelectedImportedCategoryCollection = $this->categoryResourceRepository
            ->getNotSelectedImportedCategoryCollection($activeCategoryIdsArray);

        $categories = $this->categoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('is_imported', true)
            ->addFieldToFilter('entity_id', ['nin' => $activeCategoryIdsArray]);

        foreach ($categories as $category) {
            $category->setData('import_delete', true);
            $this->categoryResourceModel->delete($category);
        }

        foreach ($notSelectedImportedCategoryCollection as $importedCategoryModel) {

            $clientCategoryId = $importedCategoryModel->getData('client_category_id');

            $categoryModel = $this->categoryFactory->create();
            $categoryModel->setData('import_delete', true);

            $isMainCategory = false;

            if (null !== $clientCategoryId) {
                $this->categoryResourceModel->load(
                    $categoryModel,
                    $clientCategoryId
                );

                if ($categoryModel->getData('parent_id') > self::ROOT_CATALOG_CATEGORY_ID
                    || (null === $categoryModel->getData('parent_id') && null !== $categoryModel->getId())
                ) {
                    $this->categoryResourceModel->delete($categoryModel);
                } else {
                    $isMainCategory = true;
                }
            }

            if (false === $isMainCategory || null === $categoryModel->getId()) {
                $importedCategoryModel->setData('client_category_id', null);
                $this->importedCategoryResourceModel->save($importedCategoryModel);
            }
        }
    }

    /**
     * @param array $dataArray
     *
     * @return array
     */
    private function removeNotNeededDataFieldsFromArray(array $dataArray)
    {
        $unusedDataFields = ['store_id', 'entity_id', 'url_path', 'url_key'];

        return array_diff_key($dataArray, array_flip($unusedDataFields));
    }
}
