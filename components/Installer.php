<?php

namespace rbacc\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Role;
use yii\rbac\Rule;
use yii\helpers\Console;
use yii\base\InvalidParamException;

/**
 * Class Installer
 *
 * Installer rbac configuration and performs application rbac installation
 *
 * @package rbacc\components
 */
class Installer extends Component
{
    /** @var \yii\rbac\BaseManager */
    protected $_auth;

    /**
     * @var Item[] All current items configured for RBAC
     */
    protected $_items = [];

    /**
     * @var array Relationships between items
     */
    protected $_children = [];

    /**
     * Creating items object using given config data collection
     *
     * @param array $collection
     * @throws InvalidConfigException
     */
    protected function generateItems(array $collection)
    {
        $this->_items = [];
        $this->_children = [];

        foreach ($collection as $c) {

            /** @var ConfigBase $c */
            if (!($c instanceof ConfigBase)) {
                throw new \RuntimeException('Invalid configuration source given.');
            }

            foreach ($c->getData() as $key => $value) {
                if (is_array($value)) {

                    if (!isset($value['type'])) {
                        throw new InvalidConfigException('Please specify type for ' . $key . ' auth item!');
                    }

                    if ($value['type'] == Item::TYPE_ROLE) {
                        $item = $this->_auth->createRole($key);
                    } elseif ($value['type'] == Item::TYPE_PERMISSION) {
                        $item = $this->_auth->createPermission($key);
                    } else {
                        throw new InvalidConfigException('Wrong type for ' . $key . ' auth item! Type can be ' . Item::TYPE_ROLE . ' (role) or ' . Item::TYPE_PERMISSION . ' (permission).');
                    }

                    $item->description = isset($value['description']) ? $value['description'] : null;
                    $item->data = isset($value['data']) ? $value['data'] : null;

                    if (isset($value['rule']) && $value['rule'] instanceof Rule) {
                        /** @var Rule $rule */
                        $rule = $value['rule'];
                        $item->ruleName = !empty($rule->name) ? $rule->name : $rule::className();
                        $this->_items[$item->ruleName] = $rule;
                    }

                    if (isset($value['children']) && is_array($value['children'])) {

                        if (!isset($this->_children[$key])) {
                            $this->_children[$key] = [];
                        }

                        foreach ($value['children'] as $kid) {
                            $this->_children[$key][] = $kid;
                        }
                    }

                } else {
                    $item = $this->_auth->createPermission($key);
                    $item->description = $value;
                }

                $this->_items[$key] = $item;
            }
        }
    }


    /**
     * Adding or deleting items if needed
     */
    protected function manageItems()
    {
        foreach ($this->_items as $item) {

            if ($item instanceof Rule) {
                $item_exist = $this->_auth->getRule($item->name);
            } elseif ($item instanceof Role) {
                $item_exist = $this->_auth->getRole($item->name);
            } elseif ($item instanceof Permission) {
                $item_exist = $this->_auth->getPermission($item->name);
            } else {
                throw new InvalidParamException('Adding unsupported object type.');
            }

            if ($item_exist) {

                if ($item_exist instanceof Rule) {
                    $item->updatedAt = $item_exist->updatedAt;
                    $need_update = serialize($item_exist) != serialize($item);
                } else {
                    $need_update = $item_exist->description != $item->description || $item_exist->ruleName != $item->ruleName || $item_exist->data != $item->data;
                }

                if ($need_update) {
                    Console::stdout("Updating $item->name item data.\n");
                    $this->_auth->update($item->name, $item);
                }

            } else {
                Console::stdout("New item added: $item->name\n");
                $this->_auth->add($item);
            }
        }

        /** @var Role|Permission|Rule $items */
        $items = ArrayHelper::merge($this->_auth->getRules(), $this->_auth->getRules(), $this->_auth->getPermissions());

        foreach ($items as $existing_item) {
            if (!isset($this->_items[$existing_item->name])) {
                Console::stdout(Console::ansiFormat('Item removed: ' . $existing_item->name . "\n", [Console::FG_RED]));
                $this->_auth->remove($existing_item);
            }
        }
    }

    /**
     * Adding or deleting children if needed
     */
    protected function manageRelations()
    {
        foreach ($this->_children as $p => $kids) {
            $parent = $this->_items[$p];

            foreach ($kids as $k) {
                $kid = $this->_items[$k];

                if (!$this->_auth->hasChild($parent, $kid) && $this->_auth->canAddChild($parent, $kid)) {
                    Console::stdout($kid->name . ' added as a child of ' . $parent->name . "\n");
                    $this->_auth->addChild($parent, $kid);
                }
            }

            foreach ($this->_auth->getChildren($p) as $current_kid) {
                if (!in_array($current_kid->name, $kids)) {
                    Console::stdout(Console::ansiFormat('Relation between  ' . $current_kid->name . ' and ' . $parent->name . " was removed!\n", [Console::FG_RED]));
                    $this->_auth->removeChild($parent, $current_kid);
                }
            }
        }
    }

    /**
     * Updates RBAC configuration using current RBAC manager component.
     *
     * @param array $collection
     * @return bool
     * @throws InvalidConfigException
     */
    public function update(array $collection)
    {
        $this->_auth = Yii::$app->authManager;
        $this->generateItems($collection);
        $this->manageItems();
        $this->manageRelations();
    }
}
