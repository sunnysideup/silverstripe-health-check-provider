<?php
use SilverStripe\Admin\CMSMenu;
use Sunnysideup\HealthCheck\Admin\HealthCheckAdmin;

CMSMenu::remove_menu_class(HealthCheckAdmin::class);
