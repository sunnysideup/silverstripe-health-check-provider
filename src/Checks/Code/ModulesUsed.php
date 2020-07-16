<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Code;

use SilverStripe\Control\Director;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class ModulesUsed extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): string
    {
        if (file_exists(Director::baseFolder() . '/composer.json')) {
            return file_get_contents(Director::baseFolder() . '/composer.json');
        }
        return '{}';
    }

    protected function nameSpacesRequired(): array
    {
        return [
            'BringYourOwnIdeas\\Maintenance',
        ];
    }
}
