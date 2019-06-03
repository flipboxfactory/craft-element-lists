<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\fields;

use craft\fields\Categories;
use flipbox\craft\element\lists\ElementList;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class CategoryList extends Categories implements SortableInterface
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
        return ElementList::t('Category List');
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
}
