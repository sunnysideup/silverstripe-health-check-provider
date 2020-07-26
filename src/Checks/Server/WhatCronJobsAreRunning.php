<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Server;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class WhatCronJobsAreRunning extends HealthCheckItemRunner
{
    public function getCalculatedAnswer()
    {
        $data = shell_exec('for user in $(cut -f1 -d: /etc/passwd); do crontab -u $user -l; done');
        $newLines = [];
        foreach(explode("\n", $data) as $line) {
            if(substr(trim($line, 0, 1)) !== '#') {
                $newLines[] = $line;
            }
        }
        return implode("\n", $newLines);
    }

    protected function nameSpacesRequired(): array
    {
        return [
            'Symbiote\\QueuedJobs',
        ];
    }
}
