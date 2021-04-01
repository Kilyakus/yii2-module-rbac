<?php
namespace kilyakus\module\rbac\commands;

use yii\console\controllers\BaseMigrateController;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\Console;

class MigrateController extends BaseMigrateController
{
    public $migrationTable = '{{%auth_migration}}';

    public $migrationPath = '@app/rbac/migrations';

    public $db = 'db';

    public $templateFile = '@dektrium/rbac/views/migration.php';

    public function init()
    {
        parent::init();

        $this->db = Instance::ensure($this->db, Connection::className());
    }

    public function getDb()
    {
        return $this->db;
    }

    protected function getMigrationHistory($limit)
    {
        if ($this->db->schema->getTableSchema($this->migrationTable, true) === null) {
            $this->createMigrationHistoryTable();
        }

        $history = (new Query())
            ->select(['apply_time'])
            ->from($this->migrationTable)
            ->orderBy(['apply_time' => SORT_DESC, 'version' => SORT_DESC])
            ->limit($limit)
            ->indexBy('version')
            ->column($this->db);

        unset($history[self::BASE_MIGRATION]);

        return $history;
    }

    protected function addMigrationHistory($version)
    {
        $this->db->createCommand()->insert($this->migrationTable, [
            'version'    => $version,
            'apply_time' => time(),
        ])->execute();
    }

    protected function removeMigrationHistory($version)
    {
        $this->db->createCommand()->delete($this->migrationTable, [
            'version' => $version,
        ])->execute();
    }

    protected function createMigrationHistoryTable()
    {
        $tableName = $this->db->schema->getRawTableName($this->migrationTable);
        $this->stdout("Creating migration history table \"$tableName\"...", Console::FG_YELLOW);
        $this->db->createCommand()->createTable($this->migrationTable, [
            'version'    => 'VARCHAR(180) NOT NULL PRIMARY KEY',
            'apply_time' => 'INTEGER',
        ])->execute();
        $this->db->createCommand()->insert($this->migrationTable, [
            'version' => self::BASE_MIGRATION,
            'apply_time' => time(),
        ])->execute();
        $this->stdout("Done.\n", Console::FG_GREEN);
    }
}