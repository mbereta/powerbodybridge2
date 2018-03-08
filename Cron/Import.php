<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Cron;

use Powerbody\Bridge\Service\Import\Task\TaskInterface;
use Powerbody\Bridge\System\Configuration\ConfigurationReaderInterface;

class Import
{
    private $tasks = [];

    private $configurationReader;

    public function __construct(
        array $tasks,
        ConfigurationReaderInterface $configurationReader
    ) {
        $this->configurationReader = $configurationReader;

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

        foreach ($this->tasks as $task) {
            $task->run();
        }
    }
}
