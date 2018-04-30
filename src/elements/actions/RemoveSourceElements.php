<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\base\FieldInterface;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\UserQuery;
use flipbox\element\lists\ElementList;
use flipbox\element\lists\records\Association;
use yii\base\Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RemoveSourceElements extends ElementAction
{
    /**
     * @var string|int
     */
    public $sourceId;

    /**
     * @var string|int
     */
    public $fieldId;

    /**
     * @return array
     */
    public function settingsAttributes(): array
    {
        return array_merge(
            parent::settingsAttributes(),
            [
                'sourceId',
                'fieldId'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return 'Remove';
    }

    /**
     * @inheritdoc
     * @param UserQuery $query
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        /** @var FieldInterface $field */
        if (null === ($field = Craft::$app->getFields()->getFieldById($this->fieldId))) {
            throw new Exception(sprintf(
                "Field %s must be an instance of '%s'",
                (string)$this->fieldId,
                (string)FieldInterface::class
            ));
        }

        if (null === ($source = Craft::$app->getElements()->getElementById($this->sourceId))) {
            throw new Exception("Element does not exist with the identifier '{$this->sourceId}'");
        }

        $siteId = Craft::$app->getSites()->getCurrentSite()->id;

        foreach ($query->all() as $target) {
            $model = new Association([
                'fieldId' => $field->id,
                'sourceId' => $source->getId(),
                'targetId' => $target->getId(),
                'siteId' => $siteId,
            ]);

            if (!ElementList::getInstance()->getSourceAssociations()->dissociate($model)) {
                throw new Exception(sprintf(
                    "Unable to disassociate element '%s' from element '%s'",
                    (string)$source->getId(),
                    (string)$target->getId()
                ));
            }
        }

        $this->setMessage(
            Craft::t(
                'element-list',
                $this->assembleMessage($query)
            )
        );

        return true;
    }

    /**
     * @param ElementQueryInterface $query
     * @return string
     */
    private function assembleMessage(ElementQueryInterface $query): string
    {
        $message = 'Element';

        if ($query->count() != 1) {
            $message = $query->count() . ' ' . $message . 's';
        }

        $message .= ' removed.';

        return Craft::t('element-list', $message);
    }
}
