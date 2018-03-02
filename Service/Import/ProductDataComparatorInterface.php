<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import;

interface ProductDataComparatorInterface
{
    public function compareResponseDataWithExisting(array $responseData) : array;
}
