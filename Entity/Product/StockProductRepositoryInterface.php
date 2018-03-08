<?php

namespace Powerbody\Bridge\Entity\Product;

interface StockProductRepositoryInterface
{
    public function getProductStockDataForSkuArray(array $skuArray): array;
}
