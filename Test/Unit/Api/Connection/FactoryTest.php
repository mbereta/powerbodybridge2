<?php

namespace Powerbody\Bridge\Test\Unit\Api\Connection;

use Powerbody\Bridge\Api\Connection\Factory;
use Powerbody\Bridge\Api\Exception\InvalidWsdlUrlException;
use Powerbody\Bridge\System\Configuration\ConfigurationReaderInterface;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testItThrowsExceptionWhileCreatingFromInvalidWsdlUrl()
    {
        $mockConfigReader = $this->getMockBuilder(ConfigurationReaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockConfigReader->expects($this->once())
            ->method('getWsdlUrl')
            ->willReturn('qweqwe');

        $this->setExpectedException(InvalidWsdlUrlException::class);

        $factory = new Factory();
        $factory->create($mockConfigReader->getWsdlUrl());
    }
}
