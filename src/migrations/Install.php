<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\craft\element\lists\migrations;

use craft\db\Migration;
use craft\records\Element;
use craft\records\Field;
use craft\records\Site;
use flipbox\craft\element\lists\records\Association;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(Association::tableName(), [
            'fieldId' => $this->integer()->notNull(),
            'sourceId' => $this->integer()->notNull(),
            'targetId' => $this->integer()->notNull(),
            'siteId' => $this->integer()->notNull(),
            'sortOrder' => $this->integer()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->addPrimaryKey(
            null,
            Association::tableName(),
            ['fieldId', 'sourceId', 'targetId', 'siteId']
        );

        $this->addForeignKey(
            null,
            Association::tableName(),
            'fieldId',
            Field::tableName(),
            'id',
            'CASCADE',
            null
        );

        $this->addForeignKey(
            null,
            Association::tableName(),
            'sourceId',
            Element::tableName(),
            'id',
            'CASCADE',
            null
        );

        $this->addForeignKey(
            null,
            Association::tableName(),
            'targetId',
            Element::tableName(),
            'id',
            'CASCADE',
            null
        );

        $this->addForeignKey(
            null,
            Association::tableName(),
            'siteId',
            Site::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
