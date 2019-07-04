<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\fields;

use craft\base\ElementInterface;
use craft\base\FieldInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @mixin FieldInterface
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

    /**
     * Identify whether a sort order should be enforced.
     *
     * @return bool
     */
    public function ensureSortOrder(): bool
    {
        return $this->sortable;
    }

    /**
     * Allow the settings to identify whether the element should be sortable
     *
     * @param bool $sortable
     * @return $this
     */
    public function setSortable(bool $sortable)
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * Get the sortable attribute value
     *
     * @return bool
     */
    public function getSortable(): bool
    {
        return $this->sortable;
    }
    
    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return null;
    }
}
