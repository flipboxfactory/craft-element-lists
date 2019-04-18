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
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        // By default, remove siteId value if it matches the primary site (to mirror what native relations is doing)
        $primarySiteId = Craft::$app->getSites()->getPrimarySite()->id;
        $siteSelect = '(case when e.siteId = '.$primarySiteId.' then null else e.siteId end) sourceSiteId';
        $joinOn = 'e.fieldId=e.fieldId AND e.sourceId=r.sourceId AND e.targetId=r.targetId';

        // Handle multi-site differently.  Match explicitly.
        if (Craft::$app->getIsMultiSite()) {
            $siteSelect = 'e.siteId';
            $joinOn .= ' AND e.siteId=r.sourceSiteId';
        }

        $query = (new Query())
            ->from(['{{%elementlist}} e'])
            ->leftJoin(
                Association::tableName() . ' r',
                $joinOn
            )
            ->select([
                'e.fieldId',
                'e.sourceId',
                'e.targetId',
                'e.sortOrder',
                'e.dateCreated',
                'e.dateUpdated',
                'e.uid',
                $siteSelect
            ])
            ->andWhere([
                'r.id' => null
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
                    'sourceSiteId'
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
