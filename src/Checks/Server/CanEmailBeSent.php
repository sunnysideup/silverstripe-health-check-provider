<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Server;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class CanEmailBeSent extends HealthCheckItemRunner
{
    #######################
    ### Names Section
    #######################

    public function nameSpacesRequired(): array
    {
        return [
            'Sunnysideup\\EmailTest',
        ];
    }
}
