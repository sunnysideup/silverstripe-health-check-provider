<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Response;

use SilverStripe\Control\Director;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class IsLiveMode extends HealthCheckItemRunner
{
    public function getCalculatedAnswer() : bool
    {
        return Director::isLive() ? true : false;
    }
}
