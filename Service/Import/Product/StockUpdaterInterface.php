<?php

namespace Powerbody\Bridge\Service\Import\Product;

interface StockUpdaterInterface
{
    public function processImport(array $productsDataArray);
}
