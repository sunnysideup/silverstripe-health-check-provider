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
                $members = $group->Members();
                foreach ($members as $member) {
                    $array[$member->ID] = [
                        'ID' => $member->ID,
                        'Name' => $member->getTitle(),
                        'Email' => $member->Email,
                    ];
                }
            }
        }

        return array_values($array);
    }
}
