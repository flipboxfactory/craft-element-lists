<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\craft\element\lists\fields;

use craft\fields\Users;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserList extends Users implements ElementListInterface
{
    use ElementListTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->inputTemplate = 'element-lists/_components/fieldtypes/ElementSource';
        $this->inputJsClass = 'Craft.NestedElementIndexSelectInput';
    }
}
