<?php

namespace Powerbody\Bridge\Entity\Manufacturer;

use Powerbody\Bridge\Api\ClientInterface;

class Repository implements RepositoryInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function findAll()
    {
        $response = $this->client->call('bridge.getManufacturers', ['for_dropclient' => true]);
        if (!is_array($response)) {
            return [];
        }

        $manufacturersArray = array_map(function ($item) {
            return new \Magento\Framework\DataObject($item);
        }, $response);

        return $manufacturersArray;
    }

    /**
     * @param array $selectedManufacturerIdsArray
     *
     * @return array
     */
    public function getSelectedManufacturerData(array $selectedManufacturerIdsArray)
    {
        $response = $this->client->call(
            'bridge.getManufacturers',
            [
                'manufacturers' => $selectedManufacturerIdsArray
            ]
        );

        if (!is_array($response)) {
            return [];
        }

        $manufacturerArray = [];
        foreach ($response as $manufacturerResponseArray) {
            $manufacturerArray[$manufacturerResponseArray['id']] = $manufacturerResponseArray;
        }

        return $manufacturerArray;
    }
}
