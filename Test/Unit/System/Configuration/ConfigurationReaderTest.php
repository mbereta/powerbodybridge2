<?php

namespace Powerbody\Bridge\Test\Unit\System\Configuration;

use Powerbody\Bridge\System\Configuration\ConfigurationReader;

class ConfigurationReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testItCanGetIfModuleIsEnabled()
    {
        $mock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('getValue')
            ->with(
                'ws_settings/general/enabled'
            )
            ->willReturn('1');

        $reader = new ConfigurationReader($mock);
        $this->assertInternalType('boolean', $reader->getIsEnabled());
    }

    public function testItCanGetApiUsernameAsString()
    {
        $mock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('getValue')
            ->with(
                'ws_settings/general/api_username'
            )
            ->willReturn(123456789);

        $reader = new ConfigurationReader($mock);
        $this->assertInternalType('string', $reader->getApiUsername());
    }

    public function testItCanGetApiPasswordAsString()
    {
        $mock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('getValue')
            ->with(
                'ws_settings/general/api_password'
            )
            ->willReturn(4567890);

        $reader = new ConfigurationReader($mock);
        $this->assertInternalType('string', $reader->getApiPassword());
    }

    public function testItCanGetApiWsdlUrlAsString()
    {
        $mock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('getValue')
            ->with(
                'ws_settings/general/api_wsdl_url'
            )
            ->willReturn(45092.2123);

        $reader = new ConfigurationReader($mock);
        $this->assertInternalType('string', $reader->getWsdlUrl());
    }
}
