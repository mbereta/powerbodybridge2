<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service\Cron;

interface ResetInterface
{

    public function deleteUnrealizedJobs();

}
