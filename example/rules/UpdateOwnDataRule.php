<?php

namespace rbacc\example\rules;

use Yii;
use yii\rbac\Rule;

/**
 * Class UpdateOwnDataRule
 *
 * User can update only own data
 *
 * @package rbacc\example\rules
 */
class UpdateOwnDataRule extends Rule
{
    public $name = 'UpdateOwnData';

    public function execute($user, $item, $params)
    {
        if (!\Yii::$app->user->isGuest) {
            return $params["user"]->id == \Yii::$app->getUser()->getId();
        }

        return false;
    }
}
