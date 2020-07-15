<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Database;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class WhatIsTheDatabaseCollation extends HealthCheckItemRunner
{

    public function getCalculatedAnswer() : string
    {
        return 'to be completed';
        //mysqli_get_charset();
    }
}
