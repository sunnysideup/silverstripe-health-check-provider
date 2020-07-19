<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Response;

use SilverStripe\ErrorPage\ErrorPage;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class IsTherePageNotFoundPage extends HealthCheckItemRunner
{
    private static $error_code = 404;

    public function getCalculatedAnswer(): array
    {
        $array = [];
        $pages = ErrorPage::get()->filter(['ErrorCode' => $this->Config()->get('error_code')]);
        foreach ($pages as $page) {
            $array[$page->ID] = [
                'Title' => $page->Title,
                'CMSEditLink' => $page->CMSEditLink(),
                'Link' => $page->Link(),
                'IsPublished' => $page->IsPublished(),
                'HtmlPageExists' => $this->htmlPageExists(),
                'HtmlLink' => $this->htmlLink(),
            ];
        }

        return $array;
    }

    protected function htmlPageExists(): bool
    {
        return file_exists($this->htmlLink());
    }

    protected function htmlLink(): string
    {
        return ASSETS_DIR . '/error-' . $this->Config()->get('error_code') . '.html';
    }

    protected function nameSpacesRequired(): array
    {
        return [
            'SilverStripe\\ErrorPage',
        ];
    }
}
