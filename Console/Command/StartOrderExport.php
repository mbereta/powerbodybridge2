<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Console\Command;

use Powerbody\Bridge\Cron\Export;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartOrderExport extends \Symfony\Component\Console\Command\Command
{
    
    /** @var \Magento\Framework\App\State */
    private $state;
    /**
     * @var Export
     */
    private $export;


    public function __construct(
        \Magento\Framework\App\State $state,
        Export $export,
        $name = null
    ) {
        $this->state = $state;

        parent::__construct($name);
        $this->export = $export;
    }
    
    protected function configure()
    {
        $this->setName('powerbodybridge:export:orders')
            ->setDescription('Start export orders')
            ->setDefinition([]);
        
        parent::configure();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);
        $this->export->run();
        $output->writeln('<info>Reset running cron jobs</info>');
    }
    
}
