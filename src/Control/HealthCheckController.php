<?php

namespace Sunnysideup\HealthCheckProvider\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Environment;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckProvider;

class HealthCheckController extends Controller
{
    private static $url_segment = 'health-check-provider';

    private static $allowed_ips = [
        '127.0.0.1',
    ];

    private static $allowed_actions = [
        'provide' => '->canProvide',
    ];

    public function provide($request)
    {
        if (! $request->param('ID')) {
            return $this->httpError(403, 'No API key provided');
        }
        if ($request->param('ID') !== Environment::getEnv('SS_HEALTH_CHECK_PROVIDER_API_KEY')) {
            return $this->httpError(403, 'Api key ' . $request->param('ID') . ' does not match');
        }
        if (! in_array($request->getIp(), $this->Config()->get('allowed_ips'), true)) {
            return $this->httpError(403, 'Ip not allowed: ' . $request->getIp());
        }

        $obj = HealthCheckProvider::create();
        $obj->write();

        $obj->Retrieved = true;
        $obj->SendNow = true;
        $obj->write();

        $this->getResponse()->addHeader('Content-type', 'application/json');
        return $obj->Data;
    }

    protected function canProvide(): bool
    {
        return Environment::getEnv('SS_HEALTH_CHECK_PROVIDER_API_KEY') ? true : false;
    }
}
