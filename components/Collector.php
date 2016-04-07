<?php
namespace rbacc\components;

use Yii;
use rbacc\Module;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class Collector
 *
 * Collects configuration data from sources specified in module $collection property
 *
 * @package rbacc\components
 */
class Collector extends Component
{
    /**
     * @var Module
     */
    protected $_module;

    /** @var  array */
    protected $_collection_data;

    /**
     * Constructor
     *
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->_module = $module;

        parent::__construct();
    }

    /**
     * Collect configs
     *
     * @return ConfigBase[]
     * @throws InvalidConfigException
     */
    protected function collect()
    {
        $result = [];

        foreach ($this->_module->collection as $c) {

            if (!class_exists($c)) {
                throw new InvalidConfigException('Class ' . $c . ' does not exist!');
            }

            $result[] = Yii::createObject($c);
        }

        return $result;
    }

    /**
     * @return array Gets data
     */
    public function getData()
    {
        if ($this->_collection_data === null) {
            $this->_collection_data = $this->collect();
        }

        return $this->_collection_data;
    }
}