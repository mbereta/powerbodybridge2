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
        return (bool) $this->scopeConfig->getValue('ws_settings/general/enabled', ScopeInterface::SCOPE_STORE);
    }
    
    public function getApiUsername() : string
    {
        return (string) $this->scopeConfig->getValue('ws_settings/general/api_username', ScopeInterface::SCOPE_STORE);

    }
    
    public function getApiPassword() : string
    {
        return (string) $this->scopeConfig->getValue('ws_settings/general/api_password', ScopeInterface::SCOPE_STORE);
    }
    
    public function getWsdlUrl() : string
    {
        return (string) $this->scopeConfig->getValue('ws_settings/general/api_wsdl_url', ScopeInterface::SCOPE_STORE);
    }
    
    public function getExportOrderStates() : array
    {
        $raw = $this->scopeConfig->getValue('pbb_orders/states/export_orders', ScopeInterface::SCOPE_STORE);
    
        return explode(',', $raw);
    }
    
    public function getUpdateOrderStates() : array
    {
        $raw = $this->scopeConfig->getValue('pbb_orders/states/update_orders', ScopeInterface::SCOPE_STORE);
    
        return explode(',', $raw);
    }

    public function getIngredientLabelWatermarkImage() : string
    {
        return (string) $this->scopeConfig->getValue('ppb_ingredients/labels/watermark_image', ScopeInterface::SCOPE_STORE);
    }

}
