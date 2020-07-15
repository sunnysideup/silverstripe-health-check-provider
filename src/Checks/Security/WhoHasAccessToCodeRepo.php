<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Security;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;
use SilverStripe\Control\Director;

class WhoHasAccessToCodeRepo extends HealthCheckItemRunner
{

    public function getCalculatedAnswer() : string
    {
        return shell_exec(
            '
                cd '.Director::baseFolder().' && git config --get remote.origin.url
            '
        );
    }
}
