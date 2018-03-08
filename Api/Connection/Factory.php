<?php

namespace Powerbody\Bridge\Api\Connection;

use Powerbody\Bridge\Api\Exception\InvalidWsdlUrlException;

class Factory implements FactoryInterface
{
    /**
     * @param string $wsdlUrl
     * @return \SoapClient
     * @throws InvalidWsdlUrlException
     */
    public function create($wsdlUrl)
    {
        if (false === filter_var($wsdlUrl,FILTER_VALIDATE_URL)) {
            throw new InvalidWsdlUrlException();
        }

        return new \SoapClient($wsdlUrl);
    }
}
