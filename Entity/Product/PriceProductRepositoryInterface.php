<?php

namespace Powerbody\Bridge\Entity\Product;

interface PriceProductRepositoryInterface
{
    public function getProductPriceDataForSkuArray(array $skuArray): array;
}
