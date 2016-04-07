# RBAC configuration stored in separate modules in Yii 2 application

This extension provides layer between Yii 2 AuthManager and access configuration that can be stored in separate config classes into your application modules.
It is useful for large projects where you have truckload of access rules, roles and other stuff.
When you organize your RBAC code in modules - you can easy manage it.
yii2-rbac-collector - is only console application component - you don't have to change something in your web application - just use Yii::$app->authManager as you used it before.


## Installation

My favorite way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require romi45/yii2-rbac-collector:~1.0
```

or add

```
"romi45/yii2-rbac-collector": "~1.0"
```
to the `require` section of your `composer.json` file.


## Configuring

Add rbacc module into your modules section in console application config.

```php
'modules' => [
    ...
    'rbacc' => [
        'class' => 'rbacc\Module',
        'collection' => [
            // Here is a list of your RBAC config classes. Example you can get in /example directory
            'app\modules\user\rbac\Config',
            'app\modules\blog\rbac\Config',
            'app\modules\hobby\rbac\YouCanNameItAsYouWant',
        ]
    ],
    ...
]

```

Here is an example of config class.
array keys - auth item name
value - auth item data

if value is not array - collector recognize it as Permission.
if value is array - you have to specify type of Item

```php

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
```

For updating RBAC configuration after some changes jus run following command:

yii rbacc/update

Collector will read your config classes and update RBAC data using your current AuthManager - PhpManager or DbManager

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.