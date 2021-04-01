<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Database;

use SilverStripe\ORM\DB;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class SizeOfDatabase extends HealthCheckItemRunner
{
    private static $fields_required = [
        'Name',
        'Engine',
        'Data_length',
        'Index_length',
        'Collation',
    ];

    public function getCalculatedAnswer(): array
    {
        $rows = DB::query('SHOW TABLE STATUS;');
        $allowedKeys = $this->Config()->get('fields_required');
        $array = $rows;
        foreach ($array as $pos => $row) {
            foreach (array_keys($row) as $key) {
                if (! in_array($key, $allowedKeys, true)) {
                    unset($array[$pos][$key]);
                }
            }
        }
        return $array;
    }
}
