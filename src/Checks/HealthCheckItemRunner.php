<?php

namespace Sunnysideup\HealthCheckProvider\Checks;

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataObject;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckAnswer;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckItem;
use Sunnysideup\HealthCheckProvider\Traits\HTMLWrappers;

class HealthCheckItemRunner
{
    use Extensible;
    use Injectable;
    use Configurable;
    use HTMLWrappers;


    protected $healthCheckItem = null;

    public function __construct(HealthCheckItem $healthCheckItem)
    {
        $this->healthCheckItem = $healthCheckItem;
    }

    public function getCalculatedAnswerLater(): string
    {
        return 'Error';
    }

    public function getIsInstalled(): bool
    {
        foreach ($this->nameSpacesRequired() as $nameSpace) {
            if (! $this->nameSpaceExists($nameSpace)) {
                return false;
            }
        }
        return true;
    }


    /**
     * Return the host website URL
     * @param  bool               $urlencode Should the URL be url encoded
     * @return string             URL of the host website
     */
    protected function getSiteURL(bool $urlencode, bool $withoutHTTP = false): string
    {
        $base = Director::absoluteBaseURL();
        if ($withoutHTTP) {
            $base = str_replace('https://', '', $base);
            $base = str_replace('http://', '', $base);
        }
        if ($urlencode) {
            $base = urlencode($base);
        }
        return $base;
    }

    protected function nameSpaceExists(string $nameSpace) : bool
    {
        $array = ClassInfo::allClasses();
        $nameSpace = rtrim($nameSpace, '\\') . '\\';
        foreach($array as $className) {
            if(stripos($className , $nameSpace) === 0) {
                return true;
            }
        }
        return false;
    }
}
