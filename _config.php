<?php

use SilverStripe\Admin\CMSMenu;
use Sunnysideup\HealthCheckProvider\Admin\HealthCheckAdmin;

CMSMenu::remove_menu_class(HealthCheckAdmin::class);
