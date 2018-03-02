<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Entity\Ingredient;

interface RepositoryInterface
{
    
    public function getIngredientsLabelImage(string $locale, array $skuArray) : array;
    
}
