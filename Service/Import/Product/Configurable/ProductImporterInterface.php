<?php

namespace Powerbody\Bridge\Service\Import\Product\Configurable;

interface ProductImporterInterface
{
    public function processImport(array $productsDataArray);

    public function disableNotRequestedProducts(array $productSkuArray);
}
