<?php
namespace kilyakus\module\rbac\components;

use yii\rbac\ManagerInterface as BaseManagerInterface;

interface ManagerInterface extends BaseManagerInterface
{
    public function getItems($type = null, $excludeItems = []);

    public function getItemsByUser($userId);

    public function getItem($name);
}