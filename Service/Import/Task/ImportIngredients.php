<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Task;

class ImportIngredients implements TaskInterface
{
    
    const IMPORT_PARTITION_SIZE = 500;
    
    /** @var \Psr\Log\LoggerInterface */
    private $logger;
    
    /** @var \Magento\Framework\Locale\ResolverInterface */
    private $localeResolver;
    
    /** @var \Powerbody\Bridge\Model\ResourceModel\ProductRepositoryInterface */
    private $productRepository;
    
    /** @var \Powerbody\Bridge\Service\LabelCreatorInterface */
    private $ingredientCreator;
    
    /** @var \Powerbody\Bridge\Service\OrderServiceInterface */
    private $entityIngredientRepository;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Powerbody\Bridge\Model\ResourceModel\ProductRepositoryInterface $productRepository,
        \Powerbody\Bridge\Service\Ingredient\LabelCreatorInterface $ingredientCreator,
        \Powerbody\Bridge\Entity\Ingredient\RepositoryInterface $entityIngredientRepository
    ) {
        $this->logger = $logger;
        $this->localeResolver = $localeResolver;
        $this->productRepository = $productRepository;
        $this->ingredientCreator = $ingredientCreator;
        $this->entityIngredientRepository = $entityIngredientRepository;
    }
    
    public function run()
    {
        $this->logger->debug(__('Started ingredients import:') . date('Y-m-d H:i:s', time()));

        $skuArray = $this->productRepository->getImportedProductsSkuArray();
        if (true === empty($skuArray)) {
            return;
        }
    
        $locale = $this->localeResolver->getLocale();
        $skuArray = array_chunk($skuArray, self::IMPORT_PARTITION_SIZE);
    
        foreach ($skuArray as $skuArrayChunk) {

            $ingredientsLabelImage = null;

            try {
                /** @var array $ingredientsLabelImage */
                $ingredientsLabelImage = $this->entityIngredientRepository->getIngredientsLabelImage(
                    $locale,
                    $skuArrayChunk
                );
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }

            if (null == $ingredientsLabelImage) {
                continue;
            }

            if (true === $this->checkResponseIsValid($ingredientsLabelImage)) {
                $this->ingredientCreator->importIngredientLabels($ingredientsLabelImage['data'][$locale]);
            }
        }

        $this->logger->debug(__('Ended ingredients import:') . date('Y-m-d H:i:s', time()));
    }
    
    public function checkResponseIsValid(array $response) : bool
    {
        $locale = $this->localeResolver->getLocale();
    
        return (true === isset($response['success']) && true === isset($response['data'][$locale]));
    }
    
}
