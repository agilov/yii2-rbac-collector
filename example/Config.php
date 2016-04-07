<?php

namespace rbacc\example;

use rbacc\components\ConfigBase;
use rbacc\example\rules\UpdateOwnDataRule;
use yii\rbac\Item;

/**
 * Class Config
 *
 * Example RBAC configuration class
 *
 * @package user\rbac
 */
class Config extends ConfigBase
{
    /**
     * Gets config data as array
     *
     * @return array
     */
    public function getData()
    {
        return [
            'user___user__view_profile' => 'View user profile',
            'user___user__view_own_profile' => [
                'type' => Item::TYPE_PERMISSION,
                'description' => 'View user own profile',
                'rule' => new UpdateOwnDataRule(),
                'children' => ['user___user__view_profile']
            ],
            'user' => [
                'type' => Item::TYPE_ROLE,
                'description' => 'User',
                'children' => ['user___user__view_own_profile'],
            ],
            'admin' => [
                'type' => Item::TYPE_ROLE,
                'description' => 'Admin',
                'children' => ['user___user__view_profile']
            ],
            'owner' => [
                'type' => Item::TYPE_ROLE,
                'description' => 'Application owner',
                'children' => ['admin'],
            ]
        ];
    }
}