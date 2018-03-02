<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Entity\Order;

interface RepositoryInterface
{
    
    public function getOrders(array $orderIds) : array;
    
    public function createOrder(array $orderData) : array;
    
}
