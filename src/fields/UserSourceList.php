<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\fields;

use Craft;
use craft\elements\User as UserElement;
use flipbox\element\lists\db\SourceUserElementQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserSourceList extends ElementSourceList
{
    /**
     * @inheritdoc
     */
    const ELEMENT_CLASS = UserElement::class;

    /**
     * The element query class
     */
    const ELEMENT_QUERY_CLASS = SourceUserElementQuery::class;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return parent::displayName() . ': ' . Craft::t('element-lists', 'Users');
    }
}
