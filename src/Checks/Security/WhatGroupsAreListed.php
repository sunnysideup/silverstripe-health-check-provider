<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Security;

use SilverStripe\Security\Group;
use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class WhatGroupsAreListed extends HealthCheckItemRunner
{
    public function getCalculatedAnswer(): array
    {
        $array = [];
        $groups = Group::get();
        foreach ($groups as $group) {
            $memberCount = $group->Members()->Count();
            if ($memberCount) {
                $array[$group->ID] = [
                    'ID' => $group->ID,
                    'Title' => $group->getTitle(),
                    'MemberCount' => $memberCount,
                ];
            }
        }

        return array_values($array);
    }
}
