<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service\Ingredient;

class LabelCreator implements LabelCreatorInterface
{
    
    /** @var \Psr\Log\LoggerInterface */
    private $logger;
    
    /** @var \Powerbody\Bridge\Model\ResourceModel\ProductRepository */
    private $productRepository;
    
    /** @var \Powerbody\Bridge\Entity\Ingredient\RepositoryInterface */
    private $ingredientRepository;
    
    /** @var \Powerbody\Ingredients\Model\ResourceModel\Ingredient\LabelRepositoryInterface */
    private $labelRepository;
    
    /** @var \Powerbody\Ingredients\Model\Ingredient\LabelFactory */
    private $labelFactory;
    
    /** @var \Powerbody\Ingredients\Model\ResourceModel\Ingredient\Label */
    private $labelResource;
    
    /** @var \Powerbody\Ingredients\Service\ImageInterface */
    private $labelImageGenerator;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Powerbody\Bridge\Model\ResourceModel\ProductRepository $productRepository,
        \Powerbody\Bridge\Entity\Ingredient\RepositoryInterface $ingredientRepository,
        \Powerbody\Ingredients\Model\Ingredient\LabelFactory $labelFactory,
        \Powerbody\Ingredients\Model\ResourceModel\Ingredient\Label $labelResource,
        \Powerbody\Ingredients\Model\ResourceModel\Ingredient\LabelRepositoryInterface $labelRepository,
        \Powerbody\Ingredients\Service\ImageInterface $labelImageGenerator
    ) {
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->ingredientRepository = $ingredientRepository;
        $this->labelRepository = $labelRepository;
        $this->labelFactory = $labelFactory;
        $this->labelResource = $labelResource;
        $this->labelImageGenerator = $labelImageGenerator;
    }
    
    public function importIngredientLabels(array $ingredientLabelsData)
    {
        $labels = $this->parseLabelData($ingredientLabelsData);
        
        foreach ($labels as $label) {
            $this->addOrUpdateLabel($label);
        }
    }
    
    private function parseLabelData(array $ingredientLabelsData) : array
    {
        $labels = [];
    
        foreach ($ingredientLabelsData as $entry) {
            $productId = $this->productRepository->getIdBySku($entry['sku']);
            if (0 === $productId) {
                continue;
            }
            
            $entry['local_product_id'] = $productId;
            $labels[] = $entry;
        }
        
        return $labels;
    }
    
    private function addOrUpdateLabel(array $labelData)
    {
        $this->labelResource->getConnection()->beginTransaction();
        
        try {
            $this->saveLabel($labelData);
            
            $this->labelResource->getConnection()->commit();
        } catch (\Exception $e) {
            $this->labelResource->getConnection()->rollBack();
            
            $this->logger->critical($e);
        }
    }
    private function saveLabel(array $labelData)
    {
        /** @var \Powerbody\Ingredients\Model\Ingredient\Label $label */
        $label = null;
        
        try {
            $label = $this->labelRepository->getByProductId($labelData['local_product_id']);
        } catch (\Exception $e) {
            $label = $this->labelFactory->create();
        }
        
        $label->addData([
            'product_id' => $labelData['local_product_id'],
        ]);
    
        $this->labelResource->save($label);
        $this->labelImageGenerator->generateImage($label, $labelData);
    }
    
}
