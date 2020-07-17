<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Content;

use SilverStripe\CMS\Model\SiteTree;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class PagesOnSite extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): int
    {
        return SiteTree::get()->count();
    }

    protected function nameSpacesRequired(): array
    {
        return [
            'SilverStripe\\Reports',
        ];
    }
}
