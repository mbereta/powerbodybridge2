<?php

namespace Powerbody\Bridge\Test\Unit\Entity\Attribute;

use Powerbody\Bridge\Api\Client;
use Powerbody\Bridge\Entity\Attribute\Repository;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsArrayOfResults()
    {
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $clientMock->expects($this->once())
            ->method('call')
            ->with('bridge.getAttributes', ['attribute_code' => [
                Repository::POWERBODY_COLOR_ATTRIBUTE_CODE,
                Repository::POWERBODY_FLAVOUR_ATTRIBUTE_CODE,
                Repository::POWERBODY_SIZE_ATTRIBUTE_CODE,
                Repository::POWERBODY_WEIGHT_ATTRIBUTE_CODE,
            ]])
            ->willReturn(array([1], [2], [3]));

        $repo = new Repository($clientMock);

        $this->assertInternalType('array', $repo->getAttributes());
    }

    public function testItAlwaysReturnsArray()
    {
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $clientMock->expects($this->atLeastOnce())
            ->method('call')
            ->with('bridge.getAttributes', ['attribute_code' => [
                Repository::POWERBODY_COLOR_ATTRIBUTE_CODE,
                Repository::POWERBODY_FLAVOUR_ATTRIBUTE_CODE,
                Repository::POWERBODY_SIZE_ATTRIBUTE_CODE,
                Repository::POWERBODY_WEIGHT_ATTRIBUTE_CODE,
            ]])
            ->willReturn(array());

        $repo = new Repository($clientMock);

        $this->assertInternalType('array', $repo->getAttributes());

        $clientMock->expects($this->once())
            ->method('call')
            ->with('bridge.getAttributes')
            ->willReturn(array());

        $this->assertInternalType('array', $repo->getAttributes());
    }
}
