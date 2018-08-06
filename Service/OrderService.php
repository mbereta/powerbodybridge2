<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service;

use  \Powerbody\Bridge\Model\Export\OrderEntryFactory;

class OrderService implements OrderServiceInterface
{

    /** @var \Powerbody\Bridge\Entity\Order\RepositoryInterface */
    private $entityOrderEntryRepository;

    /** @var \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntryRepositoryInterface */
    private $orderEntryRepository;

    /** @var \Magento\Sales\Model\OrderRepository */
    private $orderRepository;

    /** @var \Magento\Sales\Model\Order\Config */
    private $orderConfig;

    /** @var ShipmentService */
    private $shipmentService;

     /** @var OrderEntryFactory */
    private $exportOrderEntryFactory;

    public function __construct(
        \Powerbody\Bridge\Entity\Order\RepositoryInterface $entityOrderEntryRepository,
        \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntryRepositoryInterface $orderEntryRepository,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Sales\Model\Order\Config $orderConfig,
        ShipmentServiceInterface $shipmentService,
        OrderEntryFactory $exportOrderEntryFactory
    ) {
        $this->entityOrderEntryRepository = $entityOrderEntryRepository;
        $this->orderEntryRepository = $orderEntryRepository;
        $this->orderRepository = $orderRepository;
        $this->orderConfig = $orderConfig;
        $this->shipmentService = $shipmentService;
        $this->exportOrderEntryFactory = $exportOrderEntryFactory;
    }

    public function exportOrderById(int $orderId) : array
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);

        /** @var array $orderData */
        $orderData = $this->prepareOrderData($order);

        return $this->entityOrderEntryRepository->createOrder($orderData);
    }

    public function addOrdersToExportOrderEntry()
    {
        /* @var $orderCollection \Magento\Sales\Model\ResourceModel\Order\Collection */
        $orderCollection = $this->orderEntryRepository->getOrderCollectionToPush();

        /* @var $orderModel \Magento\Sales\Model\Order */
        foreach ($orderCollection as $orderModel) {
            /* @var $orderEntryModel \Powerbody\Bridge\Model\Export\OrderEntry */
            $orderEntryModel = $this->exportOrderEntryFactory->create();

            $orderEntryModel->setData([
                'id' => null,
                'order_id' => $orderModel->getData('entity_id'),
                'status' => \Powerbody\Bridge\Model\Export\OrderEntry::STATUS_NOT_PUSH,
            ]);

            try {
                $this->orderEntryRepository->save($orderEntryModel);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    public function updateOrders(array $orderIds)
    {
        $ordersData = $this->entityOrderEntryRepository->getOrders($orderIds);

        foreach ($ordersData as $orderData) {
            $this->updateOrder($orderData);
        }
    }

    private function prepareOrderData(\Magento\Sales\Model\Order $order) : array
    {
        return [
            'id' => $order->getId(),
            'status' => null,
            'currency_rate' => $order->getData('base_to_order_rate'),
            'currency' => $order->getData('order_currency_code'),
            'transport_name' => $order->getData('shipping_description'),
            'transport_price' => $order->getData('shipping_incl_tax'),
            'transport_tax' => $order->getData('shipping_tax_percent'),
            'transport_currency' => $order->getData('order_currency_code'),
            'transport_code' => '',
            'weight' => $order->getData('weight'),
            'date_add' => $order->getData('created_at'),
            'comment' => $order->getData('notes'),
            'address' => $this->prepareOrderAddressData($order),
            'billing_address' => $this->prepareOrderBillingAddressData($order),
            'products' => $this->prepareOrderItemsData($order),
            'coupon_code' => $order->getData('coupon_code'),
            'discount_amount' => $order->getData('discount_amount'),
            'discount_description' => $order->getData('discount_description'),
        ];
    }

    private function prepareOrderAddressData(\Magento\Sales\Model\Order $order) : array
    {
        /* @var \Magento\Sales\Model\Order\Address $address */
        $address = $order->getShippingAddress();

        return [
            'name' => $address->getData('firstname'),
            'surname' => $address->getData('lastname'),
            'address1' => $address->getStreetLine(1),
            'address2' => $address->getStreetLine(2),
            'address3' => $address->getStreetLine(3),
            'postcode' => $address->getData('postcode'),
            'city' => $address->getData('city'),
            'county' => null,
            'country_name' => null,
            'country_code' => $address->getData('country_id'),
            'phone' => $this->prepareOrderPhoneNumber($address),
            'email' => $order->getData('customer_email'),
        ];
    }

    private function prepareOrderBillingAddressData(\Magento\Sales\Model\Order $order): array
    {
        /* @var \Magento\Sales\Model\Order\Address $address */
        $address = $order->getBillingAddress();

        return [
            'name' => $address->getData('firstname'),
            'surname' => $address->getData('lastname'),
            'address1' => $address->getStreetLine(1),
            'address2' => $address->getStreetLine(2),
            'address3' => $address->getStreetLine(3),
            'postcode' => $address->getData('postcode'),
            'city' => $address->getData('city'),
            'county' => null,
            'country_name' => null,
            'country_code' => $address->getData('country_id'),
            'phone' => $this->prepareOrderPhoneNumber($address),
            'email' => $order->getData('customer_email'),
        ];
    }

    private function prepareOrderItemsData(\Magento\Sales\Model\Order $order) : array
    {
        $items = [];

        /* @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $items[] = [
                'product_id' => $item->getData('product_id'),
                'name' => $item->getData('name'),
                'sku' => $item->getData('sku'),
                'qty' => $item->getData('qty_ordered'),
                'price' => $item->getData('price_incl_tax'),
                'currency' => $order->getData('order_currency_code'),
                'tax' => $item->getData('tax_percent'),
                'discount_amount' => $item->getData('discount_amount'),
                'discount_percent' => $item->getData('discount_percent'),
                'discount_tax_compensation_amount' => $item->getData('discount_tax_compensation_amount'),
            ];
        }

        return $items;
    }

    private function prepareOrderPhoneNumber(\Magento\Sales\Model\Order\Address $address) : string
    {
        $telephone = $address->getData('telephone');
        $landlineTelephone = $address->getData('landline_telephone');

        return $telephone ? : ($landlineTelephone ? : '');
    }

    private function updateOrder(array $orderData)
    {
        $orderId = (int) $orderData['order_id'];

        $availableStatuses = $this->orderConfig->getStatuses();
        $status = null;

        if (true !== isset($availableStatuses[$orderData['status']])) {
            if ('processing' === $orderData['state']) {
                $status = 'processing';
            } else {
                throw new \Exception('Unknown order status');
            }
        } else {
            $status = $orderData['status'];
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);

        $order->setData('state', $orderData['state']);
        $order->setData('status', $status);

        if (false === empty($orderData['tracking_number'])) {
            $this->shipmentService->createOrUpdate($order, $orderData);
        }
    
        $this->orderRepository->save($order);
    }

}
