<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\events;

use craft\events\CancelableEvent;
use flipbox\craft\element\lists\queries\AssociationQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.2.0
 */
class CancelableQueryEvent extends CancelableEvent
{
    /**
     * @var AssociationQuery The query that has been built
     */
    public $query;
}
