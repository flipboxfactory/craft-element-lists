<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\UserQuery;
use craft\helpers\ArrayHelper;
use flipbox\craft\element\lists\ElementList;
use flipbox\craft\element\lists\records\Association;
use yii\base\Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class DissociateFromElementAction extends ElementAction
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
        return ElementList::t('Remove');
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        /** @var Field $field */
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

        // Get the count because it's cleared when dissociated
        $count = $query->count();

        foreach ($query->all() as $target) {
            if (!$record = Association::find()
                ->fieldId($field->id)
                ->sourceId($source->getId())
                ->targetId($target->getId())
                ->one()
            ) {
                continue;
            }

            if (!$record->delete()) {
                $this->setMessage(
                    $this->assembleFailMessage($query)
                );
            }
        }

        $this->setMessage($this->assembleSuccessMessage($count));
        return true;
    }

    /**
     * @param ElementQueryInterface|UserQuery $query
     * @return string
     */
    private function assembleFailMessage(ElementQueryInterface $query): string
    {
        $message = 'Failed to remove element: ';

        $users = $query->all();
        $badEmails = ArrayHelper::index($users, 'id');

        $message .= implode(", ", $badEmails);

        return ElementList::t($message);
    }

    /**
     * @param int $count
     * @return string
     */
    private function assembleSuccessMessage(int $count): string
    {
        $message = 'Element';

        if ($count != 1) {
            $message = '{count} ' . $message . 's';
        }

        $message .= ' dissociated.';

        return ElementList::t($message, ['count' => $count]);
    }
}
