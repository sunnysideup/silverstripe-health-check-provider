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
        'confirmreceipt' => '->canProvide',
    ];

    public function provide($request)
    {
        $check = $this->checkSecurity($request);
        if($check !== 'all-good') {
            return $check;
        }

        //we are ready!~
        $this->getResponse()->addHeader('Content-type', 'application/json');
        return $this->ProvideData($request);
    }

    protected function confirmreceipt()
    {
        $check = $this->checkSecurity($request);
        if($check !== 'all-good') {
            return $check;
        }
        $outcome = $this->recordReceipt($request);
        $this->getResponse()->addHeader('Content-type', 'application/json');
        return '{"Outcome": "'.$outcome.'"}';
    }

    protected function provideData()
    {
        $obj = HealthCheckProvider::create();
        $obj->write();

        $obj->Retrieved = true;
        $obj->SendNow = true;
        $obj->write();

        return $obj->Data;

    }

    protected function recordReceipt($request) : sting
    {
        $id = intval($request->param('ID'));
        $code = $request->param('OtherID');
        $obj = HealthCheck::get()->byID($id);
        $obj->ResponseCode = $code;
        $obj->write();
        if($obj->getHasErrors()) {
            $outcome = 'BAD';
        } else {
            $outcome = 'GOOD';
        }
    }

    protected function checkSecurity($request)
    {
        $headers = $request->getHeaders();
        $key = $headers['handshake'] ?? '';
        $ip = $request->getIp() ?? '';
        $outcome = HealthCheckProviderSecurity::check($key, $ip);
        if($outcome) {
            return 'all-good';
        } else {
            return $this->httpError(403, 'Sorry, we can not provide access.');
        }
    }

    protected function canProvide(): bool
    {
        return Environment::getEnv('SS_HEALTH_CHECK_PROVIDER_API_KEY') ? true : false;
    }
}
