<?php

namespace Sunnysideup\HealthCheckProvider\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Environment;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckProvider;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckProviderSecurity;

class HealthCheckController extends Controller
{
    private static $url_segment = 'health-check-provider';

    protected $editorID = 0;

    private static $allowed_actions = [
        'provide' => '->canProvide',
        'confirmreceipt' => '->canProvide',
    ];

    public function index($request) {
        return $this->httpError(404);
    }

    public function provide($request)
    {
        $check = $this->checkSecurity($request);
        if($check !== 'all-good') {
            return $check;
        }

        //we are ready!~
        $this->getResponse()->addHeader('Content-type', 'application/json');
        return $this->provideData();
    }

    public function confirmreceipt($request)
    {
        $check = $this->checkSecurity($request);
        if($check !== 'all-good') {
            return $check;
        }

        $outcome = $this->recordReceipt($request);

        $this->getResponse()->addHeader('Content-type', 'application/json');

        return '{"Outcome": "'.$outcome.'"}';
    }

    protected function provideData() : string
    {
        $obj = HealthCheckProvider::create();
        $obj->EditorID = $this->editorID;
        $obj->write();

        $obj->SendNow = true;
        $obj->write();

        return (string) $obj->Data;

    }

    protected function recordReceipt($request) : string
    {
        $id = intval($request->param('ID'));
        $code = $request->param('OtherID');
        $obj = HealthCheck::get()->byID($id);
        $obj->ReceiptCode = $code;
        $obj->Sent = true;
        $obj->write();
        if($obj->getCodesMatch()) {
            $outcome = 'BAD';
        } else {
            $outcome = 'GOOD';
        }

        return $outcome;
    }

    protected function checkSecurity($request)
    {
        $headers = $request->getHeaders();
        $key = $headers['handshake'] ?? '';
        $ip = $request->getIp();
        $outcome = HealthCheckProviderSecurity::check($key, $ip);
        if($outcome) {
            $this->editorID = HealthCheckProviderSecurity::get_editor_id($key, $ip);

            return 'all-good';
        } else {
            return $this->httpError(403, 'Sorry, we can not provide access.');
        }
    }

    protected function canProvide(): bool
    {
        return Environment::getEnv('SS_HEALTH_CHECK_PROVIDER_ALLOW_RETRIEVAL') ? true : false;
    }
}
