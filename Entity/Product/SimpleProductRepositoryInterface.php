<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Entity\Product;

interface SimpleProductRepositoryInterface
{
    public function getProductSkuForCategoryAndManufacturer(
        array $selectedManufacturerIds,
        array $selectedCategoryIds
    ) : array ;

    public function getProductDataForSkuArray(array $skuArray) : array;
}
