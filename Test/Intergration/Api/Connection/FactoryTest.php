<?php

namespace Powerbody\Bridge\Test\Intergration\Api\Connection;

use Powerbody\Bridge\Api\Connection\Factory;
use Powerbody\Bridge\Api\Exception\InvalidWsdlUrlException;
use Powerbody\Bridge\System\Configuration\ConfigurationReaderInterface;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testItCanCreateConnection()
    {
        $mockConfigReader = $this->getMockBuilder(ConfigurationReaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockConfigReader->expects($this->once())
            ->method('getWsdlUrl')
            ->willReturn('https://www.powerbody.co.uk/index.php/api/soap/?wsdl');

        $factory = new Factory();
        $this->assertInstanceOf('\SoapClient', $factory->create($mockConfigReader->getWsdlUrl()));
    }
}
