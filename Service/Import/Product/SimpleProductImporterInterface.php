<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Product;

interface SimpleProductImporterInterface
{
    public function processImport(array $productDataArray);

    public function disableNotRequestedProducts(array $productSkuArray);
}
