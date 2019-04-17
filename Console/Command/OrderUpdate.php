<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Console\Command;

use Powerbody\Bridge\Cron\UpdateOrders;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrderUpdate extends \Symfony\Component\Console\Command\Command
{
    
    /** @var \Magento\Framework\App\State */
    private $state;

    private $updateOrders;

    public function __construct(
        \Magento\Framework\App\State $state,
        UpdateOrders $updateOrders,
        $name = null
    ) {
        $this->state = $state;
        $this->updateOrders = $updateOrders;

        parent::__construct($name);
    }
    
    protected function configure()
    {
        $this->setName('powerbodybridge:order:update')
            ->setDescription('Order update')
            ->setDefinition([]);
        
        parent::configure();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);
        $this->updateOrders->run();
    }
    
}
