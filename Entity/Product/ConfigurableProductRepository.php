<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Entity\Product;

use Powerbody\Bridge\Api\ClientInterface;
use Psr\Log\LoggerInterface;

class ConfigurableProductRepository
{
    const CHUNK_SIZE = 500;

    private $client;
    private $logger;

    public function __construct(ClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function getSkuArray(array $manufacturers = [], array $categories = []) : array
    {
        $params = [
            'manufacturers_ids' => $manufacturers,
            'categories_ids'  => $categories,
        ];
        $response = $this->client->call('bridge.getConfigurableProductsSku', $params);
        if (!is_array($response) || !$this->client->checkResponseIsValid($response)) {
            $this->logger->error('Response data is not valid');
            return [];
        }
        $products = [];
        foreach ($response['data'] as $sku => $date) {
            $products[$sku] = $date['updated_at'];
        }

        return $products;
    }

    public function findBySku(array $skuArray) : array
    {
        $skuArray = array_values($skuArray);
        $productsBySku = [];
        foreach (array_chunk($skuArray, self::CHUNK_SIZE) as $chunk) {
            $params = ['product_sku' => array_values($chunk)];
            $response = $this->client->call('bridge.getConfigurableProductList', $params);
            if (!is_array($response) || !$this->client->checkResponseIsValid($response)) {
                $this->logger->error('Response data is not valid');
                continue;
            }
            $productsBySku = array_merge($productsBySku, $response['data']);
        }

        return $productsBySku;
    }
}
