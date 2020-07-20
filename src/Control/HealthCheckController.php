<?php

namespace Sunnysideup\HealthCheckProvider\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Environment;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckProvider;

class HealthCheckController extends Controller
{
    private static $url_segment = 'health-check-provider';

    private static $allowed_actions = [
        'provide' => '->canProvide',
    ];

    public function provide($request)
    {
        if (! $request->param('ID')) {
            return $this->httpError(403, 'No API key provided');
        }
        if ($request->param('ID') !== Environment::getEnv('SS_HEALTH_CHECK_PROVIDER_API_KEY')) {
            return $this->httpError(403, 'Api key does not match');
        }

        $obj = HealthCheckProvider::create();
        $id = $obj->write();

        $obj->Retrieved = true;
        $obj->SendNow = true;
        $obj->write();

        $this->getResponse()->addHeader('Content-type', 'application/json');
        return $obj->Data;
    }

    protected function canProvide(): bool
    {
        return Environment::getEnv('SS_HEALTH_CHECK_PROVIDER_API_KEY') ? true : fal;
        se;
    }
}
