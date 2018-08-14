<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetCronCommand extends \Symfony\Component\Console\Command\Command
{
    
    /** @var \Magento\Framework\App\State */
    private $state;
    
    /** @var \Powerbody\Bridge\Service\Cron\ResetInterface */
    private $cronReset;
    
    public function __construct(
        \Magento\Framework\App\State $state,
        \Powerbody\Bridge\Service\Cron\ResetInterface $cronReset,
        $name = null
    ) {
        $this->state = $state;
        $this->cronReset = $cronReset;
        
        parent::__construct($name);
    }
    
    protected function configure()
    {
        $this->setName('powerbodybridge:reset:cron')
            ->setDescription('Reset running cron jobs')
            ->setDefinition([]);
        
        parent::configure();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cronReset->deleteUnrealizedJobs();
        $output->writeln('<info>Reset running cron jobs</info>');
    }
    
}
