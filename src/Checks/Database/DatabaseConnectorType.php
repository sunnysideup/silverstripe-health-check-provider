<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Database;

use Exception;

use SilverStripe\ORM\DatabaseAdmin;
use SilverStripe\Core\Environment;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class DatabaseConnectorType extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): string
    {
        return Environment::getEnv('SS_DATABASE_CLASS');
    }
}
