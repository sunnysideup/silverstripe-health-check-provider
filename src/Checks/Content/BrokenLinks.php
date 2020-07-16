<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Content;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class BrokenLinks extends HealthCheckItemRunner
{
    protected function nameSpacesRequired(): array
    {
        return [
            'SilverStripe\\ExternalLinks',
        ];
    }
}
