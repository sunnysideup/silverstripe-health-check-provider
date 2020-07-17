<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Code;

use SilverStripe\Control\Director;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class ModulesUsed extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): array
    {
        $array = json_decode($this->getData(), true);
        if ($array && is_array($array)) {
            return $array['require'] ?? [];
        }
    }

    protected function getData(): string
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
