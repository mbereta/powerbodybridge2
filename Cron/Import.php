<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Cron;

use Powerbody\Bridge\Service\Import\Task\TaskInterface;
use Powerbody\Bridge\System\Configuration\ConfigurationReaderInterface;

use \Psr\Log\LoggerInterface as Logger;

class Import
{
    private $tasks = [];

    private $configurationReader;


    protected $logger;

    public function __construct(
        array $tasks,
        ConfigurationReaderInterface $configurationReader,
        Logger $logger
    ) {
        $this->configurationReader = $configurationReader;
        $this->logger = $logger;
        foreach ($tasks as $task) {
            if ($task instanceof TaskInterface) {
                $this->tasks[] = $task;
            }
        }
    }

    public function run()
    {
        if (false === $this->configurationReader->getIsEnabled()) {
            return $this;
        }

        $i=0;
        foreach ($this->tasks as $task) {
            $this->logger->info("przed taskiem: " . $i++);

            $task->run();
        }
    }
}
