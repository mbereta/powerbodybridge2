<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service;

class ArrayUniquenessProvider implements ArrayUniquenessProviderInterface
{
    public function arrayUniqueMultidimensional(array $multidimensionalArray) : array
    {
        return array_map('unserialize', array_unique(array_map('serialize', $multidimensionalArray)));
    }
}
