<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\fields;

use craft\base\ElementInterface;
use craft\elements\db\ElementQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
interface RelationalInterface
{
    /**
     * @param ElementInterface|null $element
     * @return ElementQueryInterface
     */
    public function getQuery(ElementInterface $element = null): ElementQueryInterface;

    /**
     * Attempt to resolve an element that can be associated to this field.  For example, we may have
     * and Id or an array which can be used to look up an element
     *
     * @param string|int|array $element
     * @return ElementInterface|null
     */
    public function resolveElement($element);
}
