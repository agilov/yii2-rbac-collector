<?php

namespace rbacc;

use rbacc\components\Collector;
use Yii;

/**
 * Class Module
 *
 * RBAC collector module
 *
 * @package rbacc
 */
class Module extends \yii\base\Module
{
    /**
     * @var string
     */
    public $controllerNamespace = 'rbacc\commands';

    /**
     * @var array
     */
    public $collection = [];

    /**
     * Perform configuration collection for installer
     *
     * @return array
     */
    public function configData()
    {
        return (new Collector($this))->getData();
    }
}