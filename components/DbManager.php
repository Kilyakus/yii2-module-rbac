<?php
namespace kilyakus\module\rbac\components;

use yii\db\Query;
use yii\rbac\DbManager as BaseDbManager;

class DbManager extends BaseDbManager implements ManagerInterface
{
    public function getItems($type = null, $excludeItems = [])
    {
        $query = (new Query())
            ->from($this->itemTable);

        if ($type !== null) {
            $query->where(['type' => $type]);
        } else {
            $query->orderBy('type');
        }

        foreach ($excludeItems as $name) {
            $query->andWhere('name != :item', ['item' => $name]);
        }

        $items = [];

        foreach ($query->all($this->db) as $row) {
            $items[$row['name']] = $this->populateItem($row);
        }

        return $items;
    }

    public function getItemsByUser($userId)
    {
        if (empty($userId)) {
            return [];
        }

        $query = (new Query)->select('b.*')
            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
            ->andWhere(['a.user_id' => (string) $userId]);

        $roles = [];
        foreach ($query->all($this->db) as $row) {
            $roles[$row['name']] = $this->populateItem($row);
        }
        return $roles;
    }

    public function getItem($name)
    {
        return parent::getItem($name);
    }
}