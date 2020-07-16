<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Security;

use SilverStripe\Security\Permission;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class WhoHasFullAccessRightsToSite extends HealthCheckItemRunner
{
    private static $access_code = [
        'ADMIN',
    ];

    public function getCalculatedAnswer(): array
    {
        $array = [];
        $groups = Permission::get_groups_by_permission($this->Config()->get('access_code'));
        foreach ($groups as $group) {
            if ($group->Members()->count()) {
                $group->Members()->map('Title', 'Email');
                if ($map) {
                    foreach ($map->toArray() as $name => $email) {
                        $array[$email] = [
                            'Name' => $name,
                            'Email' => $email,
                        ];
                    }
                }
            }
        }

        return array_values($array);
    }
}
