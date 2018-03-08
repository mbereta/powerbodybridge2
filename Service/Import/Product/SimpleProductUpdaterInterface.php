<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Product;

use \Magento\Catalog\Model\Product;

interface SimpleProductUpdaterInterface
{
    public function createOrUpdate(Product $productModel, array $productDataArray);
}
