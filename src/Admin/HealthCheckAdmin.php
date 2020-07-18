<?php

namespace Sunnysideup\HealthCheckProvider\Admin;

use SilverStripe\Admin\ModelAdmin;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckItemProvider;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckProvider;

class HealthCheckAdmin extends ModelAdmin
{
    private static $managed_models = [
        HealthCheckProvider::class,
        HealthCheckItemProvider::class,
    ];

    private static $url_segment = 'health';

    private static $menu_title = 'Health Check';

    private static $menu_icon_class = 'font-icon-checklist';
}
