<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Model\ResourceModel\Product\Attribute;

class TextValueRepository implements TextValueRepositoryInterface
{
    
    /** @var \Powerbody\Bridge\Model\Product\Attribute\TextValueFactory */
    private $modelFactory;
    
    /** @var TextValue */
    private $resourceModel;
    
    public function __construct(
        \Powerbody\Bridge\Model\Product\Attribute\TextValueFactory $modelFactory,
        TextValue $resourceModel
    ) {
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
    }
    
    public function getInstance(int $storeId, int $productId, int $attributeId) : \Powerbody\Bridge\Model\Product\Attribute\TextValue
    {
        /** @var \Powerbody\Bridge\Model\Product\Attribute\TextValue $textValueModel */
        $textValueModel = $this->modelFactory->create();
        
        try {
            $valueIds = $textValueModel->getCollection()
                ->addFieldToFilter('entity_id', $productId)
                ->addFieldToFilter('attribute_id', $attributeId)
                ->addFieldToFilter('store_id', $storeId)
                ->getAllIds();
            $valueId = reset($valueIds);
            
            $this->resourceModel->load($textValueModel, $valueId, 'value_id');
        } catch (\Execption $e) {}
        
        return $textValueModel;
    }
    
    public function save(\Powerbody\Bridge\Model\Product\Attribute\TextValue $model)
    {
        $this->resourceModel->save($model);
    }
    
}
