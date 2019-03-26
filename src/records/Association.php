<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\records;

use Craft;
use flipbox\craft\ember\records\ActiveRecord;
use flipbox\craft\ember\records\FieldAttributeTrait;
use flipbox\craft\ember\records\SiteAttributeTrait;
use flipbox\craft\ember\records\SortableTrait;
use flipbox\craft\element\lists\queries\AssociationQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $targetId
 * @property int $sourceId
 */
class Association extends ActiveRecord
{
    use SiteAttributeTrait,
        FieldAttributeTrait,
        SortableTrait;

    /**
     * @inheritdoc
     */
    const TABLE_ALIAS = 'elementlist';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['fieldId', 'siteId'];

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return AssociationQuery
     */
    public static function find(): AssociationQuery
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        return Craft::createObject(AssociationQuery::class, [get_called_class()]);
    }

    /*******************************************
     * RULES
     *******************************************/

    /**
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            $this->siteRules(),
            $this->fieldRules(),
            [
                [
                    [
                        'targetId',
                        'sourceId',
                        'fieldId',
                    ],
                    'required'
                ],
                [
                    'targetId',
                    'unique',
                    'targetAttribute' => [
                        'targetId',
                        'sourceId',
                        'fieldId',
                        'siteId'
                    ]
                ],
                [
                    [
                        'targetId',
                        'sourceId',
                    ],
                    'safe',
                    'on' => [
                        static::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->ensureSortOrder(
            [
                'sourceId' => $this->sourceId,
                'fieldId' => $this->fieldId,
                'siteId' => $this->siteId
            ]
        );

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->autoReOrder(
            'targetId',
            [
                'sourceId' => $this->sourceId,
                'fieldId' => $this->fieldId,
                'siteId' => $this->siteId
            ]
        );

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        $this->sequentialOrder(
            'targetId',
            [
                'sourceId' => $this->sourceId,
                'fieldId' => $this->fieldId,
                'siteId' => $this->siteId
            ]
        );

        parent::afterDelete();
    }
}
