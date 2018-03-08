<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Entity\Product;

use \Psr\Log\LoggerInterface;
use \Powerbody\Bridge\Api\ClientInterface;

class StockProductRepository implements StockProductRepositoryInterface
{
    private $client;

    protected $logger;

    public function __construct(ClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function getProductStockDataForSkuArray(array $skuArray) : array
    {
        $response = $this->client->call(
            'bridge.getProductsQty',
            [
                'sku' => $skuArray,
                'all_products' => true
            ]
        );

        if (false === is_array($response)) {
            $this->logger->error('Response data is not valid');
            return [];
        }

        return $response;
    }
}
