<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Security;

use SilverStripe\Security\Group;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;
use SilverStripe\Core\Environment;

class IsDefaultAdminPasswordSafe extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): bool
    {
        return $this->checkPassword(Environment::getEnv('SS_DEFAULT_ADMIN_USERNAME'));
    }
}
