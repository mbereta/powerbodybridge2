<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service;

interface OrderServiceInterface
{
    
    public function exportOrderById(int $orderId) : array;
    
    public function updateOrders(array $orderIds);
    
}
