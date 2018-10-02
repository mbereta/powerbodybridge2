<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service;

use \Magento\Framework\App\ResourceConnection;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory as TaxClassCollectionFactory;

class TaxService implements TaxServiceInterface
{

    const DEFAULT_TAX_COUNTRY = 'GB';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $taxClassMapper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        TaxClassCollectionFactory $taxClassCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection
    ) {
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->taxClassMapper = $this->getTaxClassMapper();
    }

    public function getTaxClassIdByRate(float $rate) : int
    {
        $key = array_search($rate, $this->taxClassMapper);

        if (false === $key) {
            return (int) $this->taxClassMapper['default'];
        }

        return (int) $key;
    }

    public function getTaxClassMapper() : array
    {
        $taxClassArray = [];

        /* @var $taxClassCollection \Magento\Tax\Model\ResourceModel\TaxClass\Collection */
        $taxClassCollection = $this->taxClassCollectionFactory->create();
        $taxClassCollection->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT);
        $taxClassCollection->getSelect()
            ->join(
                ['tc' => $this->resourceConnection->getTableName('tax_calculation')],
                'main_table.class_id = tc.product_tax_class_id',
                []
           )
           ->join(
                ['tcr' => $this->resourceConnection->getTableName('tax_calculation_rate')],
                'tc.tax_calculation_rate_id = tcr.tax_calculation_rate_id ',
                ['rate']
           )
           ->where('tcr.tax_country_id = "'. self::DEFAULT_TAX_COUNTRY .'" AND tax_region_id = 0');

        foreach ($taxClassCollection as $taxClassModel) {
            $taxClassArray[$taxClassModel->getData('class_id')] = floatval($taxClassModel->getData('rate'));
        }

        $taxClassArray['default'] = $this->scopeConfig
            ->getValue('tax/classes/default_product_tax_class', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $taxClassArray;
    }
}
