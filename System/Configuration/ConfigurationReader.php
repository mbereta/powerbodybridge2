<?php

namespace Powerbody\Bridge\System\Configuration;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigurationReader implements ConfigurationReaderInterface
{

    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getIsEnabled() : bool
    {
        return $this->scopeConfig->isSetFlag('ws_settings/general/enabled');
    }

    public function getApiUsername() : string
    {
        return (string) $this->scopeConfig->getValue('ws_settings/general/api_username');
    }

    public function getApiPassword() : string
    {
        return (string) $this->scopeConfig->getValue('ws_settings/general/api_password');
    }

    public function getWsdlUrl() : string
    {
        return (string) $this->scopeConfig->getValue('ws_settings/general/api_wsdl_url');
    }

    public function getExportOrderStates() : array
    {
        return explode(',', $this->scopeConfig->getValue('pbb_orders/states/export_orders'));
    }

    public function getUpdateOrderStates() : array
    {
        return explode(',', $this->scopeConfig->getValue('pbb_orders/states/update_orders'));
    }

    public function getIngredientLabelWatermarkImage() : string
    {
        return (string) $this->scopeConfig->getValue('ppb_ingredients/labels/watermark_image');
    }

    public function getHttpAuthLogin(): string
    {
        return (string) $this->scopeConfig->getValue('ws_settings/general/api_wsdl_http_auth_login');
    }

    public function getHttpAuthPassword(): string
    {
        return (string) $this->scopeConfig->getValue('ws_settings/general/api_wsdl_http_auth_password');
    }
}
