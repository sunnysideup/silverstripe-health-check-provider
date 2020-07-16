<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Database;

use SilverStripe\ORM\DB;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class SizeOfDatabase extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): array
    {
        $rows = DB::query('SHOW TABLE STATUS;');
        $array = [];
        foreach ($rows as $row) {
            $array = $row;
        }

        return $array;
    }
}
