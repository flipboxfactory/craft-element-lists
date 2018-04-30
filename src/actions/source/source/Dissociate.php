<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\actions\source;

use flipbox\craft\sortable\associations\records\SortableAssociationInterface;
use flipbox\element\lists\ElementList;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Dissociate extends Action
{
    /**
     * @param Model|SortableAssociationInterface $model
     * @return bool
     * @throws \Exception
     */
    protected function performAction(Model $model): bool
    {
        return ElementList::getInstance()->getSourceAssociations()->dissociate($model);
    }
}
