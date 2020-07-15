<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Code;

use SilverStripe\Core\Environment;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class AreThereAnyStartupErrors extends HealthCheckItemRunner
{

    public function getCalculatedAnswer(): bool
    {
        if (Environment::getEnv('SS_ALLOW_SMOKE_TEST')) {
            return true;
        }
        return false;
    }

    public function nameSpacesRequired(): array
    {
        return [
            'Sunnysideup\\TemplateOverview\\',
        ];
    }
}
