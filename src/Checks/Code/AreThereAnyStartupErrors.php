<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Code;

use SilverStripe\Core\Environment;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class AreThereAnyStartupErrors extends HealthCheckItemRunner
{
    public function IsEnabled(): bool
    {
        return (bool) Environment::getEnv('SS_ALLOW_SMOKE_TEST');
    }

    protected function nameSpacesRequired(): array
    {
        return [
            'Sunnysideup\\TemplateOverview\\',
        ];
    }
}
