<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Entity\Category;

use Powerbody\Bridge\Api\ClientInterface;
use Magento\Store\Api\Data\StoreInterface;
use Powerbody\Bridge\Service\CategoryCreator as CategoryCreatorService;

class Repository implements RepositoryInterface
{
    private $client;

    private $store;

    public function __construct(
        ClientInterface $client,
        StoreInterface $store
    ) {
        $this->client = $client;
        $this->store = $store;
    }

    public function findAll() : array
    {
        $response = $this->client->call('bridge.getAllCategories', ['for_dropclient' => true]);
        if (!is_array($response)) {
            return [];
        }

        $categoriesArray = array_map(function ($item) {
            return new \Magento\Framework\DataObject($item);
        }, $response);

        return $categoriesArray;
    }

    public function getSelectedCategoryData(array $selectedCategoryIdsArray) : array
    {
        $response = $this->client->call(
            'bridge.getAllCategories',
            [
                'categories' => $selectedCategoryIdsArray,
                'locale'     => $this->store->getLocaleCode()
            ]
        );

        if (!is_array($response)) {
            return [];
        }

        $categoriesArray = [];
        foreach ($response as $categoryResponseArray) {
            if ($categoryResponseArray['entity_id'] > CategoryCreatorService::ROOT_CATALOG_CATEGORY_ID) {
                $categoriesArray[$categoryResponseArray['entity_id']] = $categoryResponseArray;
            }
        }

        return $categoriesArray;
    }
}
