<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixDescriptionsCommand extends \Symfony\Component\Console\Command\Command
{
    
    /** @var \Magento\Framework\App\State */
    private $state;
    
    /** @var \Powerbody\Bridge\Service\Fixer\ConfigurableDescriptionInterface */
    private $configurableDescription;
    
    public function __construct(
        \Magento\Framework\App\State $state,
        \Powerbody\Bridge\Service\Fixer\ConfigurableDescriptionInterface $configurableDescription,
        $name = null
    ) {
        $this->state = $state;
        $this->configurableDescription = $configurableDescription;
        
        parent::__construct($name);
    }
    
    protected function configure()
    {
        $this->setName('powerbodybridge:fix:descriptions')
            ->setDescription('Fixes descriptions in configurable products')
            ->setDefinition([]);
        
        parent::configure();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMIN);
        
        $this->configurableDescription->fixDescriptions();
        $output->writeln('<info>Done</info>');
    }
    
}
