<?php

namespace rbacc\components;

use yii\base\Object;

/**
 * Class ConfigSource
 *
 * @package rbacc\components
 */
abstract class ConfigBase extends Object
{
    /**
     * @return array
     */
    public abstract function getData();
}