<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Server;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class WhatCronJobsAreRunning extends HealthCheckItemRunner
{

    public function getCalculatedAnswer()
    {
        return shell_exec('for user in $(cut -f1 -d: /etc/passwd); do crontab -u $user -l; done');
    }

    public function nameSpacesRequired(): array
    {
        return [
            'Symbiote\\QueuedJobs',
        ];
    }
}
