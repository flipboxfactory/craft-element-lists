<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\migrations;

use Craft;
use craft\db\Migration;
use flipbox\craft\element\lists\records\Association;
use yii\db\Query;

class m190413_125316_relations extends Migration
{
    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $query = (new Query())
            ->from(['{{%elementlist}}'])
            ->select([
                'fieldId',
                'sourceId',
                'targetId',
                'sortOrder',
                'dateCreated',
                'dateUpdated',
                'uid',
                'siteId as sourceSiteId',
            ]);

        foreach ($query->batch(1000) as $batch) {
            Craft::$app->getDb()->createCommand()->batchInsert(
                Association::tableName(),
                [
                    'fieldId',
                    'sourceId',
                    'targetId',
                    'sortOrder',
                    'dateCreated',
                    'dateUpdated',
                    'uid',
                    'siteId'
                ],
                $batch,
                false
            )->execute();
        }

        $this->dropTableIfExists('elementlist');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}
