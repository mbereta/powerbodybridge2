<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Entity\Order;

use Powerbody\Bridge\Api\ClientInterface;

class Repository implements RepositoryInterface
{
    
    /** @var ClientInterface */
    private $client;
    
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }
    
    public function getOrders(array $orderIds) : array
    {
        $requestData = [
            'from' => null,
            'to' => null,
            'ids' => $orderIds,
        ];
    
        return $this->client->call(
            'dropshipping.getOrders',
            $requestData
        );
    }
    
    public function createOrder(array $orderData) : array
    {
        $response = $this->client->call(
            'dropshipping.createOrder',
            $orderData
        );
        
        return [
            'api_response' => $response['api_response'],
            'api_response_error' => $response['api_response_error'],
        ];
    }
    
}
