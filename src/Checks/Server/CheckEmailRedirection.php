<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Server;

use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Environment;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class CheckEmailRedirection extends HealthCheckItemRunner
{

    public function getCalculatedAnswer()
    {
        $array['admin_email'] = Config::inst()->get(Email::class, 'admin_email');
        $array['send_all_emails_from'] = $this->mergeConfiguredEmails(
            'send_all_emails_from',
            'SS_SEND_ALL_EMAILS_FROM'
        );
        $array['send_all_emails_to'] = $this->mergeConfiguredEmails(
            'send_all_emails_to',
            'SS_SEND_ALL_EMAILS_TO'
        );
        $array['cc_all_emails_to'] = $this->mergeConfiguredEmails(
            'cc_all_emails_to',
            'SS_CC_ALL_EMAILS_TO'
        );
        $array['bcc_all_emails_to'] = $this->mergeConfiguredEmails(
            'bcc_all_emails_to',
            'SS_BCC_ALL_EMAILS_TO'
        );
        foreach ($array as $key => $value) {
            if (! $value) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Normalise email list from config merged with env vars
     * COPIED FROM Email::class
     *
     * @param string $config Config key
     * @param string $env Env variable key
     *
     * @return string Array of email addresses
     */
    protected function mergeConfiguredEmails(string $config, string $env): string
    {
        // Normalise config list
        $normalised = [];
        $source = (array) Email::config()->get($config);
        foreach ($source as $address => $name) {
            if ($address && ! is_numeric($address)) {
                $normalised[$address] = $name;
            } elseif ($name) {
                $normalised[$name] = null;
            }
        }
        $extra = Environment::getEnv($env);
        if ($extra) {
            $normalised[$extra] = null;
        }
        return implode(', ', $normalised);
    }
}
