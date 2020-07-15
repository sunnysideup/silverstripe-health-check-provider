<?php

namespace Sunnysideup\HealthCheckProvider\Admin;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\GridField\GridField;
use Sunnysideup\HealthCheckProvider\Model\HealthCheck;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckAnswer;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckGroup;
use Sunnysideup\HealthCheckProvider\Model\HealthCheckItem;

use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class HealthCheckAdmin extends ModelAdmin
{
    private static $managed_models = [
        HealthCheck::class,
        HealthCheckItem::class,
        HealthCheckAnswer::class,
    ];

    private static $url_segment = 'health';

    private static $menu_title = 'health check';

    private static $menu_icon_class = 'font-icon-checklist';

}
