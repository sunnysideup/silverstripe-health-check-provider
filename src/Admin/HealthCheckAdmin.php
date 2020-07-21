<?php

namespace Sunnysideup\HealthCheckProvider\Admin;

use SilverStripe\Admin\ModelAdmin;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckItemProvider;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckProvider;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckProviderSecurity;

class HealthCheckAdmin extends ModelAdmin
{
    private static $managed_models = [
        HealthCheckProviderSecurity::class,
        HealthCheckProvider::class,
        HealthCheckItemProvider::class,
    ];

    private static $url_segment = 'health';

    private static $menu_title = 'Health Check';

    private static $menu_icon_class = 'font-icon-checklist';
}
