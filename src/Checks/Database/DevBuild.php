<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Database;

use Exception;

use SilverStripe\ORM\DatabaseAdmin;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class DevBuild extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): string
    {
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
        } catch (Exception $e) {
            $answer = 'Error retrieving data: ' . $e->getMessage();
        }

        return $answer;
    }
}
