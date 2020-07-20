<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Content;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class PagesOnSite extends HealthCheckItemRunner
{
    private static $limit = 3;

    public function getCalculatedAnswer(): array
    {
        $pageTypes = ClassInfo::subclassesFor(SiteTree::class, false);
        $array = [];
        foreach ($pageTypes as $pageType) {
            $pages = Versioned::get_by_stage(
                $pageType,               // class,
                Versioned::LIVE,               // stage,
                '',                             // filter = '',
                DB::get_conn()->random(),       // sort = '',
                '',                             // join = '',
                $this->config()->get('limit')   // limit
            );

            $array[] = [
                'Name' => Injector::inst()->get($pageType)->i18n_singular_name(),
                'Count' => $pageType::get()->count(),
                'Examples' => $this->turnPagesIntoArray($pages),
            ];
        }
        return $array;
    }

    protected function nameSpacesRequired(): array
    {
        return [
            'SilverStripe\\Reports',
        ];
    }
}
