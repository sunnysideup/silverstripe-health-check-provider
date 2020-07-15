<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Response;

use SilverStripe\ErrorPage\ErrorPage;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class IsTherePageNotFoundPage extends HealthCheckItemRunner
{

    private static $error_code = 404;

    protected function getCalculatedAnswer() : array
    {
        $array = [];
        $pages = ErrorPage::get()->filter(['ErrorCode' => $this->Config()->get('error_code')]);
        foreach($pages as $page) {
            $array[$page->ID] = [
                'CMSEditLink' => $page->CMSEditLink(),
                'Link' => $page->Link(),
                'HTMLPageExists' => $this->htmlPageExists(),
            ];
        }

        return $array;
    }

    protected function htmlPageExists(): bool
    {
        return file_exists(ASSETS_DIR . '/error-' . $this->Config()->get('error_code') . '.html');
    }
}
