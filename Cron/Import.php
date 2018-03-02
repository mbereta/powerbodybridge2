<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Cron;

use Powerbody\Bridge\Service\Import\Task\TaskInterface;

class Import
{
    private $tasks = [];

    public function __construct(array $tasks)
    {
        foreach ($tasks as $task) {
            if ($task instanceof TaskInterface) {
                $this->tasks[] = $task;
            }
        }
    }

    public function run()
    {
        foreach ($this->tasks as $task) {
            $task->run();
        }
    }
}
