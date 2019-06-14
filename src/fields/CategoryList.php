<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\fields;

use craft\elements\Category;
use craft\fields\Categories;
use flipbox\craft\element\lists\ElementList;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class CategoryList extends Categories implements SortableInterface, RelationalInterface
{
    use ElementListTrait;

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

    /**
     * @inheritDoc
     * @return Category|null
     */
    public function resolveElement($element)
    {
        if (is_numeric($element)) {
            return \Craft::$app->getCategories()->getCategoryById($element);
        }

        return Category::findOne($element);
    }
}
