<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Console\Command;

use Powerbody\Bridge\Cron\Import;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartProductImport extends \Symfony\Component\Console\Command\Command
{
    
    /** @var \Magento\Framework\App\State */
    private $state;

    /** @var $import Import */
    private $import;
    
    public function __construct(
        \Magento\Framework\App\State $state,
        Import $import,
        $name = null
    ) {
        $this->state = $state;
        $this->import = $import;
        
        parent::__construct($name);
    }
    
    protected function configure()
    {
        $this->setName('powerbodybridge:import:products')
            ->setDescription('Start product import')
            ->setDefinition([]);
        
        parent::configure();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);
        $this->import->run();
        $output->writeln('<info>Reset running cron jobs</info>');
    }
    
}
