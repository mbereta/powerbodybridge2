<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Console\Command;

use Powerbody\Bridge\Cron\StockData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartStockImport extends \Symfony\Component\Console\Command\Command
{
    
    /** @var \Magento\Framework\App\State */
    private $state;

    /** @var $import Import */
    private $stockData;
    
    public function __construct(
        \Magento\Framework\App\State $state,
        StockData $stockData,
        $name = null
    ) {
        $this->state = $state;
        $this->stockData = $stockData;
        
        parent::__construct($name);
    }
    
    protected function configure()
    {
        $this->setName('powerbodybridge:import:stock')
            ->setDescription('Start stock import')
            ->setDefinition([]);
        
        parent::configure();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);
        $this->stockData->run();
    }
    
}
