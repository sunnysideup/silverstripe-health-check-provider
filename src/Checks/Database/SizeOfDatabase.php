<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Database;

use SilverStripe\Assets\File;
use SilverStripe\ORM\DB;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class SizeOfDatabase extends HealthCheckItemRunner
{

    public function getCalculatedAnswer() : array
    {
        $rows = DB::query('SHOW TABLE STATUS;');
        $size = 0;
        $array = [];
        foreach ($rows as $row) {
            $array = $row;
        }

        return $array;
    }
}
