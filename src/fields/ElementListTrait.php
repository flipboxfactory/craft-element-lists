<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\fields;

use craft\base\ElementInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
trait ElementListTrait
{
    use ModifyElementQueryTrait,
        NormalizeValueTrait,
        InputTrait;

    /**
     * @var bool
     */
    protected $ignoreSearchKeywords = true;

    /**
     * @inheritdoc
     */
    public function getSearchKeywords($value, ElementInterface $element): string
    {
        if ($this->ignoreSearchKeywords === true) {
            return '';
        }

        return parent::getSearchKeywords($value, $element);
    }
}
