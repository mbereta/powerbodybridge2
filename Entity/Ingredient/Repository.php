<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Entity\Ingredient;

class Repository implements RepositoryInterface
{
    
    /** @var \Powerbody\Bridge\Api\ClientInterface */
    private $client;
    
    public function __construct(
        \Powerbody\Bridge\Api\ClientInterface $client
    ) {
        $this->client = $client;
    }
    
    public function getIngredientsLabelImage(string $locale, array $skuArray) : array
    {
        $response = $this->client->call('bridge.getIngredientsLabelData', [
            'locale' => $locale,
            'sku' => $skuArray,
        ]);
        
        if (false === $this->checkResponseIsValid($response)) {
            throw new \Exception('Wrong response from WebService.');
        }
    
        return $response;
    }
    
    /**
     * @param mixed $response
     *
     * @return bool
     */
    private function checkResponseIsValid($response) : bool
    {
        if (false === is_array($response) || true !== isset($response['success'])) {
            return false;
        }
        
        return true;
    }
    
}
