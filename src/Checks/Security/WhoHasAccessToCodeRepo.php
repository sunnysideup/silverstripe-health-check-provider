<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Security;

use SilverStripe\Control\Director;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class WhoHasAccessToCodeRepo extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): string
    {
        return trim(shell_exec(
            '
                cd ' . Director::baseFolder() . ' && git config --get remote.origin.url
            '
        ));
    }
}
