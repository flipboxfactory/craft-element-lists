<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\fields;

use craft\elements\Entry;
use craft\fields\Entries;
use flipbox\craft\element\lists\ElementList;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class EntryList extends Entries implements SortableInterface, RelationalInterface
{
    use ElementListTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->settingsTemplate = 'element-lists/_components/fieldtypes/settings';
        $this->inputTemplate = 'element-lists/_components/fieldtypes/input';
        $this->inputJsClass = 'Craft.NestedElementIndexSelectInput';
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return ElementList::t('Entry List');
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
     * @inheritDoc
     * @return Entry|null
     */
    public function resolveElement($element)
    {
        if (is_numeric($element)) {
            return \Craft::$app->getEntries()->getEntryById($element);
        }

        return Entry::findOne($element);
    }
}
