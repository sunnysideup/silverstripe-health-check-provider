<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Server;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class CanEmailBeSent extends HealthCheckItemRunner
{
    #######################
    ### Names Section
    #######################

    protected function nameSpacesRequired(): array
    {
        return [
            'Sunnysideup\\EmailTest',
        ];
    }
}
