<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service;

interface ArrayUniquenessProviderInterface
{
    public function arrayUniqueMultidimensional(array $attributeValuesArray) : array;
}
