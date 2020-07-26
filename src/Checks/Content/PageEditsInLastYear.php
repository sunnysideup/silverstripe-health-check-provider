<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Content;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class PageEditsInLastYear extends HealthCheckItemRunner
{
    private static $limit = 3;

    public function getCalculatedAnswer(): array
    {
        $returnArray = [];
        $rows = DB::query('SELECT unix_timestamp(LastEdited) as A FROM SiteTree_Versions');
        foreach($rows as $row) {
            $returnArray[] = $row['A'];
        }

        return $returnArray;
    }

    protected function nameSpacesRequired(): array
    {
        return [
            'SilverStripe\\Reports',
        ];
    }
}
