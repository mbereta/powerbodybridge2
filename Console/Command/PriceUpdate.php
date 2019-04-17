<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Console\Command;

use Powerbody\Bridge\Cron\PriceData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PriceUpdate extends \Symfony\Component\Console\Command\Command
{
    
    /** @var \Magento\Framework\App\State */
    private $state;

    private $priceData;

    public function __construct(
        \Magento\Framework\App\State $state,
        PriceData $priceData,
        $name = null
    ) {
        $this->state = $state;
        $this->priceData = $priceData;

        parent::__construct($name);
    }
    
    protected function configure()
    {
        $this->setName('powerbodybridge:update:prices')
            ->setDescription('Start update prices')
            ->setDefinition([]);
        
        parent::configure();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);
        $this->priceData->run();
        $output->writeln('<info>Reset running cron jobs</info>');
    }
    
}
