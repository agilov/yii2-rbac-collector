<?php
namespace rbacc\commands;


use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use rbacc\components\Installer;
use rbacc\Module;

/**
 * Class UpdateController
 *
 * User interface to update RBAC configuration
 *
 * @property Module $module
 *
 * @package rbacc\controllers
 */
class UpdateController extends Controller
{
    /**
     * RBAC configuration installation
     */
    public function actionIndex()
    {
        if ($this->confirm('Do you want to update RBAC configuration?')) {
            $this->stdout('Performing RBAC update...' . "\n");
            $installer = new Installer();
            $installer->update($this->module->configData());
            $this->stdout('RBAC configuration up to date!' . "\n", Console::FG_GREEN);
        }
    }
}
