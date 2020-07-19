<?php

namespace Sunnysideup\HealthCheckProvider\Checks;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
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
}
