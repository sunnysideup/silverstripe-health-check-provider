<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Database;

use Exception;

use SilverStripe\ORM\DatabaseAdmin;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class DevBuild extends HealthCheckItemRunner
{
    private static $include_dev_build = false;

    public function getCalculatedAnswer(): string
    {
        if ($this->Config()->get('include_dev_build')) {
            $db = new DatabaseAdmin();
            $start = microtime(true);
            $answer = '';
            try {
                $db->doBuild(true, false, false);
                $end = microtime(true) - $start;

                $answer = sprintf(
                    'Completed in %s seconds',
                    round($end, 2)
                );
            } catch (Exception $exception) {
                $answer = 'Error retrieving data: ' . $exception->getMessage();
            }

            return $answer;
        }
        return '
                Feature not enabled on host.
                Please set DevBuild::$include_dev_build = true in yml config files.
            ';
    }
}
