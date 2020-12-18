<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Database;

use SilverStripe\ORM\DB;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class MysqlVersion extends HealthCheckItemRunner
{

    public function getCalculatedAnswer(): string
    {
        $output = shell_exec('mysql -V');
        preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
        return $version[0];
    }
}
