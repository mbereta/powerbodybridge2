<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service;

interface TaxServiceInterface
{

    public function getTaxClassIdByRate(float $rate) : int;

    public function getTaxClassMapper() : array;
    
}
