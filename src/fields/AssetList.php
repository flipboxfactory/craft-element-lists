<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\fields;

use craft\fields\Assets;
use flipbox\craft\element\lists\ElementList;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class AssetList extends Assets
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

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return ElementList::t('Asset List');
    }
}
