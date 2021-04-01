<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Database;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class MysqlVersion extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): string
    {
        $output = shell_exec('mysql -V');
        preg_match('#\d+\.\d+\.\d+#', $output, $version);
        return $version[0];
    }
}
