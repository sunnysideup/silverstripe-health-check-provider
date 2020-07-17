<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Response;

use SilverStripe\Control\Middleware\CanonicalURLMiddleware;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class SslEnabled extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): bool
    {
        return CanonicalURLMiddleware::singleton()->getForceSSL() ? true : false;
    }
}
