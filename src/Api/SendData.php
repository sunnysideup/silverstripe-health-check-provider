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

    private const URL = 'check.silverstripe-webdevelopment.com/report/newreport';

    protected $data = '';

    public function setData(string $data)
    {
        $this->data = $data;
    }

    public function send(): string
    {
        try {
            $curl = curl_init(self::URL);

            # Setup request to send json via POST.
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

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
