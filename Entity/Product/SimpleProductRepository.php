<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Entity\Product;

use \Psr\Log\LoggerInterface;
use \Powerbody\Bridge\Api\ClientInterface;

class SimpleProductRepository implements SimpleProductRepositoryInterface
{
    const TYPE_ID_SIMPLE = 1;
    const POWERBODY_CO_UK_CODE = 'powerbody_co_uk';
    const CHUNK_SIZE = 500;

    private $client;

    protected $logger;

    public function __construct(ClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function getProductSkuForCategoryAndManufacturer(
        array $selectedManufacturerIds,
        array $selectedCategoryIds) : array
    {
        $response = $this->client->call(
            'bridge.getProductSKUsForDropclient',
            [
                'manufacturers_ids' => $selectedManufacturerIds,
                'categories_ids'    => $selectedCategoryIds,
            ]
        );

        if (
            false === is_array($response)
            || false === $this->client->checkResponseIsValid($response)
        ) {
            $this->logger->error('Response data is not valid');
            return [];
        }

        return $response['data'];
    }

    public function getProductDataForSkuArray(array $productSkuArray) : array
    {
        $productsBySku = [];
        foreach (array_chunk($productSkuArray, self::CHUNK_SIZE) as $chunk) {        
            $response = $this->client->call(
                'bridge.getProductsForDropclient',
                [
                    'type_id'       => self::TYPE_ID_SIMPLE,
                    'code'          => self::POWERBODY_CO_UK_CODE,
                    'product_sku'   => implode(',', $chunk),
                ]
            );

            if (
                false === is_array($response)
                || false === $this->client->checkResponseIsValid($response)
            ) {
                $this->logger->error('Response data is not valid');
                return [];
            }

            $productsBySku = array_merge($productsBySku, $response['data']);
        }
        
        return $productsBySku;
    }
}
