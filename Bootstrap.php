<?php
namespace kilyakus\module\rbac;

use kilyakus\module\rbac\components\DbManager;
use kilyakus\module\rbac\components\ManagerInterface;
use dektrium\user\Module as UserModule;
use yii\base\Application;
use yii\web\Application as WebApplication;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;

class Bootstrap implements BootstrapInterface
{
    const VERSION = '1.0.0-alpha';

    public function bootstrap($app)
    {
        if (!isset($app->get('i18n')->translations['rbac*'])) {
            $app->get('i18n')->translations['rbac*'] = [
                'class'    => 'yii\i18n\PhpMessageSource',
                'basePath' => __DIR__ . '/messages',
            ];
        }

        if ($this->checkRbacModuleInstalled($app)) {
            $authManager = $app->get('authManager', false);

            if (!$authManager) {
                $app->set('authManager', [
                    'class' => DbManager::className(),
                ]);
            } else if (!($authManager instanceof ManagerInterface)) {
                throw new InvalidConfigException('You have wrong authManager configuration');
            }

            if ($this->checkUserModuleInstalled($app) && $app instanceof WebApplication) {
                $app->getModule('rbac')->admins = $app->getModule('user')->admins;
            }   
        }
    }

    protected function checkRbacModuleInstalled(Application $app)
    {
        if ($app instanceof WebApplication) {
            return $app->hasModule('rbac') && $app->getModule('rbac') instanceof RbacWebModule;
        } else {
            return $app->hasModule('rbac') && $app->getModule('rbac') instanceof RbacConsoleModule;
        }
    }

    protected function checkUserModuleInstalled(Application $app)
    {
        return $app->hasModule('user') && $app->getModule('user') instanceof UserModule;
    }

    protected function checkAuthManagerConfigured(Application $app)
    {
        return $app->authManager instanceof ManagerInterface;
    }
}
