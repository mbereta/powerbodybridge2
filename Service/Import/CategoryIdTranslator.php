<?php

namespace Powerbody\Bridge\Service\Import;

use Powerbody\Bridge\Model\Imported\CategoryFactory as ImportedCategoryFactory;

class CategoryIdTranslator implements IdTranslatorInterface
{
    private $importedCategoryFactory;

    public function __construct(ImportedCategoryFactory $categoryFactory)
    {
        $this->importedCategoryFactory = $categoryFactory;
    }

    public function translateBaseToClientIds(array $baseCategoryIdsArray) : array
    {
        $importedCategoryCollection = $this->importedCategoryFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('base_category_id', ['in' => $baseCategoryIdsArray])
            ->addFieldToFilter('client_category_id', ['neq' => null]);

        return $importedCategoryCollection->getColumnValues('client_category_id');
    }
}
