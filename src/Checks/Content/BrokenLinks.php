<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Content;

use SilverStripe\ExternalLinks\Tasks\CheckExternalLinksTask;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckAnswer;

class BrokenLinks extends HealthCheckItemRunner
{

    public function nameSpacesRequired(): array
    {
        return [
            'SilverStripe\\ExternalLinks',
        ];
    }
}
