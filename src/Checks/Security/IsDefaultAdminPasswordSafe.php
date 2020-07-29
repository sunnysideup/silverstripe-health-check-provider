<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Security;

use SilverStripe\Security\Group;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class IsDefaultAdminPasswordSafe extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): bool
    {
        return $this->checkPassword(Environment::getEnv('SS_DEFAULT_ADMIN_USERNAME'));
    }
}
