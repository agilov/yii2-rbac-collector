<?php

namespace rbacc\components;

/**
 * Class ConfigSource
 *
 * @package rbacc\components
 */
abstract class ConfigBase
{
    /**
     * @return array
     */
    public abstract function getData();
}
