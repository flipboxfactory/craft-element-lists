<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\fields;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\events\FieldElementEvent;
use flipbox\craft\element\lists\relationships\RelationshipInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property string $settingsTemplate
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
    public function setSortable(bool $sortable = null)
    {
        $this->sortable = $sortable === true;
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
     * @inheritDoc
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'element-lists/_components/fieldtypes/settings',
            [
                'settingsTemplate' => $this->settingsTemplate,
                'field' => $this,
            ]
        );
    }

    /**
     * Our value is not an ElementQueryInterface and therefore we should handle it
     * differently.
     *
     * @inheritdoc
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        // Skip if the element is just propagating, and we're not localizing relations
        /** @var Element $element */
        if (!$element->propagating || $this->localizeRelations) {
            /** @var RelationshipInterface $value */
            $value = $element->getFieldValue($this->handle);

            if ($value->isMutated()) {
                $value->save();
            }
        }

        // Trigger an 'afterElementSave' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_ELEMENT_SAVE)) {
            $this->trigger(self::EVENT_AFTER_ELEMENT_SAVE, new FieldElementEvent([
                'element' => $element,
                'isNew' => $isNew,
            ]));
        }
    }
}
