<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\fields;

use craft\elements\User;
use craft\fields\Users;
use flipbox\craft\element\lists\ElementList;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserList extends Users implements SortableInterface, RelationalInterface
{
    use ElementListTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->inputTemplate = 'element-lists/_components/fieldtypes/input';
        $this->inputJsClass = 'Craft.NestedElementIndexSelectInput';
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return ElementList::t('User List');
    }

    /**
     * @inheritdoc
     */
    public function settingsAttributes(): array
    {
        return array_merge(
            parent::settingsAttributes(),
            [
                'sortable'
            ]
        );
    }

    /**
     * inheritDoc
     * @return User|null
     */
    public function resolveElement($element)
    {
        if (is_numeric($element)) {
            return \Craft::$app->getUsers()->getUserById($element);
        }

        if (is_string($element)) {
            return \Craft::$app->getUsers()->getUserByUsernameOrEmail($element);
        }

        return User::findOne($element);
    }
}
