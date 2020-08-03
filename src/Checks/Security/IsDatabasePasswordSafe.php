<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Security;

use SilverStripe\Core\Environment;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class IsDatabasePasswordSafe extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): bool
    {
        return $this->checkPassword(Environment::getEnv('SS_DATABASE_PASSWORD'));
    }
}
