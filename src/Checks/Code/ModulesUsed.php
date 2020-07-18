<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Code;

use SilverStripe\Control\Director;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class ModulesUsed extends HealthCheckItemRunner
{
    private static $fields_required_from_installation_file = [
        'name',
        'version',
    ];

    public function getCalculatedAnswer(): array
    {
        //get data from
        $json = $this->getDataFromComposerFile();
        $installed = $this->getDataFromInstalledFile();
        return [
            'Require' => $json['require'] ?? [],
            'Installed' => $installed ?? [],
        ];
    }

    protected function getDataFromComposerFile(): array
    {
        return $this->getDataFromFile('composer.json');
    }

    protected function getDataFromInstalledFile(): array
    {
        $array = $this->getDataFromFile('vendor/composer/installed.json');
        $keysRequired = $this->Config()->get('fields_required_from_installation_file');
        foreach ($array as $key => $item) {
            foreach (array_keys($item) as $innerKey) {
                if (! in_array($innerKey, $keysRequired, true)) {
                    unset($array[$key][$innerKey]);
                }
            }
        }
        return $array;
    }

    protected function getDataFromFile(string $relativeFilePath): array
    {
        $json = '{}';
        $path = Director::baseFolder() . '/' . $relativeFilePath;
        if (file_exists($path)) {
            $json = file_get_contents($path);
            if ($json) {
                $array = @json_decode($json, true);
                if ($array && is_array($array)) {
                    return $array;
                }
            }
        }
        return [];
    }

    protected function nameSpacesRequired(): array
    {
        return [
            'BringYourOwnIdeas\\Maintenance',
        ];
    }
}
