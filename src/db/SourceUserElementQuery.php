<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\db;

use craft\elements\db\UserQuery;
use flipbox\craft\sortable\associations\db\SortableAssociationQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.1
 */
class SourceUserElementQuery extends UserQuery implements SortableAssociationQueryInterface
{
}
