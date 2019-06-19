<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\records;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\errors\FieldNotFoundException;
use flipbox\craft\element\lists\fields\SortableInterface;
use flipbox\craft\ember\records\ActiveRecord;
use flipbox\craft\ember\records\FieldAttributeTrait;
use flipbox\craft\ember\records\SortableTrait;
use flipbox\craft\element\lists\queries\AssociationQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $targetId
 * @property int $sourceId
 * @property int $sortOrder
 */
class Association extends ActiveRecord
{
    use SourceAttributeTrait,
        TargetAttributeTrait,
        SourceSiteAttributeTrait,
        FieldAttributeTrait,
        SortableTrait;

    /**
     * @inheritdoc
     */
    const TABLE_ALIAS = 'relations';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['fieldId', 'siteId', 'sourceSiteId', 'sourceId', 'targetId'];

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
            $this->sourceSiteRules(),
            $this->fieldRules(),
            $this->sourceRules(),
            $this->targetRules(),
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
                        'sourceSiteId'
                    ]
                ]
            ]
        );
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            $this->sourceAttributeLabels(),
            $this->targetAttributeLabels()
        );
    }

    /**
     * @inheritdoc
     *
     * @throws FieldNotFoundException
     */
    public function beforeSave($insert)
    {
        if ($this->getSortableField()->ensureSortOrder()) {
            $this->ensureSortOrder(
                [
                    'sourceId' => $this->sourceId,
                    'fieldId' => $this->fieldId,
                    'sourceSiteId' => $this->sourceSiteId
                ]
            );
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     *
     * @throws FieldNotFoundException
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->getSortableField()->ensureSortOrder()) {
            $this->autoReOrder(
                'targetId',
                [
                    'sourceId' => $this->sourceId,
                    'fieldId' => $this->fieldId,
                    'sourceSiteId' => $this->sourceSiteId
                ]
            );
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     *
     * @throws FieldNotFoundException
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        if ($this->getSortableField()->ensureSortOrder()) {
            $this->sequentialOrder(
                'targetId',
                [
                    'sourceId' => $this->sourceId,
                    'fieldId' => $this->fieldId,
                    'sourceSiteId' => $this->sourceSiteId
                ]
            );
        }

        parent::afterDelete();
    }

    /**
     * @return SortableInterface
     * @throws FieldNotFoundException
     */
    protected function getSortableField(): SortableInterface
    {
        if (!$this->getField() instanceof SortableInterface) {
            throw new FieldNotFoundException(sprintf(
                "Field must be an instance of '%s', '%s' given.",
                SortableInterface::class,
                get_class($this->getField())
            ));
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getField();
    }


    /*******************************************
     * RESOLVERS
     *******************************************/

    /**
     * @param string $attribute
     * @return Element|ElementInterface|null
     */
    protected function resolveElementFromIdAttribute(string $attribute)
    {
        if (null === $this->{$attribute}) {
            return null;
        }

        return Craft::$app->getElements()->getElementById($this->{$attribute});
    }

    /**
     * @param mixed $element
     * @return Element|ElementInterface|null
     */
    protected function resolveElement($element = null)
    {
        if ($element === null) {
            return null;
        }

        if ($element instanceof ElementInterface) {
            return $element;
        }

        if (is_numeric($element)) {
            return Craft::$app->getElements()->getElementById($element);
        }

        return null;
    }
}
