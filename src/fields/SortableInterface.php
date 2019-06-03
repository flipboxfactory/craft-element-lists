<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\fields;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.1.3
 */
interface SortableInterface
{
    /**
     * Identify whether a sort order should be enforced.  When enforced,
     * after saving a record, the sort order will be evaluated.  If it isn't in proper order,
     * records will be updated with a sequential sort order.
     *
     * Note: when working with large data sets (thousands or relations), enforcing sort order may take a long
     * time to execute and affect performance.
     *
     * @return bool
     */
    public function ensureSortOrder(): bool;
}
