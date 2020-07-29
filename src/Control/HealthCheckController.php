<?php

namespace Sunnysideup\HealthCheckProvider\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Environment;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckProvider;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckProviderSecurity;

class HealthCheckController extends Controller
{
    protected $editorID = 0;

    private static $url_segment = 'health-check-provider';

    private static $allowed_actions = [
        'provide' => '->canProvide',
        'confirmreceipt' => '->canProvide',
    ];

    public function index($request)
    {
        return $this->httpError(404);
    }

    public function provide($request)
    {
        $check = $this->checkSecurity($request);
        if ($check !== 'all-good') {
            return $check;
        }

        //we are ready!~
        $this->getResponse()->addHeader('Content-type', 'application/json');
        return $this->provideData();
    }

    public function confirmreceipt($request)
    {
        $check = $this->checkSecurity($request);
        if ($check !== 'all-good') {
            return $check;
        }

        $outcome = $this->recordReceipt($request);

        $this->getResponse()->addHeader('Content-type', 'application/json');

        return '{"Success": ' . $outcome . '}';
    }

    protected function provideData(): string
    {
        $obj = HealthCheckProvider::create();
        $obj->EditorID = $this->editorID;
        $obj->SendNow = true;
        $id = $obj->write();
        sleep(2);
        HealthCheckProvider::get()->byID($id);
        return (string) $obj->Data;
    }

    protected function recordReceipt($request): bool
    {
        $success = false;
        $id = intval($request->param('ID'));
        $code = $request->param('OtherID');
        /** @var HealthCheckProvider|null */
        $obj = HealthCheckProvider::get()->byID($id);
        if ($obj) {
            if (! $code) {
                $code = 'no code provided';
            }
            $obj->ResponseCode = $code;
            $obj->Sent = true;
            $obj->SendNow = false;
            $obj->write();
            $success = $obj->getCodesMatch();
        }

        return $success;
    }

    protected function checkSecurity($request)
    {
        $headers = $request->getHeaders();
        $key = $headers['handshake'] ?? '';
        $ip = $request->getIp();
        $outcome = HealthCheckProviderSecurity::check($key, $ip);
        if ($outcome) {
            $this->editorID = HealthCheckProviderSecurity::get_editor_id($key, $ip);

            return 'all-good';
        }
        return $this->httpError(403, 'Sorry, we can not provide access.');
    }

    protected function canProvide(): bool
    {
        if (Environment::getEnv('SS_HEALTH_CHECK_PROVIDER_ALLOW_RETRIEVAL')) {
            return true;
        }
        die('Please set SS_HEALTH_CHECK_PROVIDER_ALLOW_RETRIEVAL to use this facility.');
    }
}
