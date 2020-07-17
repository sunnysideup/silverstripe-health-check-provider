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
        $array = [];
        $allowedKeys = $this->Config()->get('fields_required');
        foreach ($rows as $row) {
            $array[] = $row;
        }
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
