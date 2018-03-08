<?php

namespace Powerbody\Bridge\Api\Connection;

interface FactoryInterface
{
    /**
     * @param $wsdlUrl
     * @return \SoapClient
     */
    public function create($wsdlUrl);
}
