<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Code;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;
use SilverStripe\Control\Director;

class ModulesUsed extends HealthCheckItemRunner
{

    public function getCalculatedAnswer(): string
    {
        if( file_exists(Director::baseFolder() . '/composer.json')) {
            return file_get_contents(Director::baseFolder() . '/composer.json');
        }
        return '{}';
    }

    public function nameSpacesRequired(): array
    {
        return [
            'BringYourOwnIdeas\\Maintenance',
        ];
    }
}
