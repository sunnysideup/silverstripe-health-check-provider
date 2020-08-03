<?php

namespace Sunnysideup\HealthCheckProvider\Checks;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\SS_List;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckItemProvider;

class HealthCheckItemRunner
{
    use Extensible;
    use Injectable;
    use Configurable;

    protected $healthCheckItemProvider = null;

    public function __construct(HealthCheckItemProvider $healthCheckItemProvider)
    {
        $this->healthCheckItemProvider = $healthCheckItemProvider;
    }

    public function IsInstalled(): bool
    {
        foreach ($this->nameSpacesRequired() as $nameSpace) {
            if (! $this->nameSpaceExists($nameSpace)) {
                return false;
            }
        }
        return true;
    }

    public function IsEnabled(): bool
    {
        return $this->IsInstalled();
    }

    /**
     * @return mixed
     */
    public function getCalculatedAnswer()
    {
        return '';
    }

    protected function nameSpacesRequired(): array
    {
        return [];
    }

    protected function nameSpaceExists(string $nameSpace): bool
    {
        $array = ClassInfo::allClasses();
        $nameSpace = rtrim($nameSpace, '\\') . '\\';
        foreach ($array as $className) {
            if (stripos($className, $nameSpace) === 0) {
                return true;
            }
        }
        return false;
    }

    protected function turnPagesIntoArray(SS_List $pages): array
    {
        $array = [];
        foreach ($pages as $page) {
            if ($page->IsPublished()) {
                $array[$page->ID] = [
                    'MenuTitle' => $page->MenuTitle,
                    'CMSEditLink' => $page->CMSEditLink(),
                    'Link' => $page->Link(),
                ];
            }
        }

        return $array;
    }

    protected function checkPassword(string $pwd): bool
    {
        if (strlen($pwd) < 16) {
            return false;
        }

        if (! preg_match('#[0-9]+#', $pwd)) {
            return false;
        }

        if (! preg_match('#[a-zA-Z]+#', $pwd)) {
            return false;
        }

        return true;
    }
}
