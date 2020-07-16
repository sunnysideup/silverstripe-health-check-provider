<?php

namespace Sunnysideup\HealthCheckProvider\Api;

use Exception;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;

class SendData
{
    use Extensible;
    use Injectable;
    use Configurable;

    protected $data = '';

    private static $url = 'check.silverstripe-webdevelopment.com/report/newreport';

    public function setData(string $data)
    {
        $this->data = $data;
    }

    public function send(): string
    {
        try {
            $curl = curl_init($this->Config()->get('url'));

            # Setup request to send json via POST.
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);

            # Return response instead of printing.
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            # Send request.
            $result = curl_exec($curl);
            curl_close($curl);

            # return response.
            return (string) $result;
        } catch (Exception $exception) {
            return 'Caught exception: ' . $exception->getMessage();
        }
    }
}
