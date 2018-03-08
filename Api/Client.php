<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Api;

use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Powerbody\Bridge\Api\Connection\FactoryInterface;
use Powerbody\Bridge\System\Configuration\ConfigurationReaderInterface;

class Client implements ClientInterface
{
    
    const API_RESPONSE_SUCCESS = 'SUCCESS';
    
    private $soapClient;

    private $jsonDecoder;

    private $jsonEncoder;

    private $configurationReader;

    public function __construct(
        FactoryInterface $clientFactory,
        DecoderInterface $jsonDecoder,
        EncoderInterface $jsonEncoder,
        ConfigurationReaderInterface $configurationReader
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->configurationReader = $configurationReader;
        $this->soapClient = $clientFactory->create($configurationReader->getWsdlUrl());
    }

    public function call(string $method, array $params = null) : array
    {
        if (null !== $params) {
            $params = $this->jsonEncoder->encode($params);
        }

        $session = $this->soapClient->login(
            $this->configurationReader->getApiUsername(),
            $this->configurationReader->getApiPassword()
        );
        if (null !== $params) {
            $result = $this->soapClient->call($session, $method, $params);
        } else {
            $result = $this->soapClient->call($session, $method);
        }

        $this->soapClient->endSession($session);

        if (true === is_array($result)) {
            return $result;
        }

        return $this->jsonDecoder->decode($result);
    }

    public function checkResponseIsValid(array $response) : bool
    {
        return (true === is_array($response)
            && true === isset($response['success'])
            && true === $response['success']
            && true === isset($response['data'])
            && true === is_array($response['data'])
        );
    }
}
