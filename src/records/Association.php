<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
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
     *
     * @deprecated
     */
    const TARGET_ATTRIBUTE = 'targetId';

    /**
     * @inheritdoc
     *
     * @deprecated
     */
    const SOURCE_ATTRIBUTE = 'sourceId';

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
                'targetId' => $this->targetId,
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
            'sourceId',
            [
                'targetId' => $this->targetId,
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
            'sourceId',
            [
                'targetId' => $this->targetId,
                'fieldId' => $this->fieldId,
                'siteId' => $this->siteId
            ]
        );

        parent::afterDelete();
    }
}
