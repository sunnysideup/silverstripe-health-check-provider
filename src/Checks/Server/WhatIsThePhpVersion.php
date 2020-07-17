<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Server;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class WhatIsThePhpVersion extends HealthCheckItemRunner
{
    #######################
    ### Names Section
    #######################

    public function getCalculatedAnswer()
    {
        return PHP_VERSION;
    }
}
