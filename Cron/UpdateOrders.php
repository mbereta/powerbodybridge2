<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Cron;

use Powerbody\Bridge\Service\Import\Task\TaskInterface;

class UpdateOrders
{
    
    /** @var \Powerbody\Bridge\Service\Export\Task\TaskInterface */
    private $tasks = [];
    
    public function __construct(array $tasks)
    {
        foreach ($tasks as $task) {
            if (true === ($task instanceof TaskInterface)) {
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
