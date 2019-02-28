<?php

namespace Powerbody\Bridge\System\Configuration;

interface ConfigurationReaderInterface
{
    
    public function getIsEnabled() : bool;
    
    public function getApiUsername() : string;
    
    public function getApiPassword() : string;
    
    public function getWsdlUrl() : string;
    
    public function getExportOrderStates() : array;
    
    public function getUpdateOrderStates() : array;
    
    public function getIngredientLabelWatermarkImage() : string;

    public function getHttpAuthLogin() : string;

    public function getHttpAuthPassword() : string;
}
