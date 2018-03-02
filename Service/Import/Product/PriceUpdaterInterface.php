<?php

namespace Powerbody\Bridge\Service\Import\Product;

interface PriceUpdaterInterface
{
    public function processImport(array $priceDataArray);
}
