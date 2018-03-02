<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Model\ResourceModel\Product\Attribute;

interface TextValueRepositoryInterface
{
    
    public function getInstance(int $storeId, int $productId, int $attributeId) : \Powerbody\Bridge\Model\Product\Attribute\TextValue;
    
    public function save(\Powerbody\Bridge\Model\Product\Attribute\TextValue $model);
    
}
