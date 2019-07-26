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
use craft\helpers\StringHelper;
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
     * Prepares the field’s value to be stored somewhere, like the content table or JSON-encoded in an entry
     * revision table.
     *
     * Data types that are JSON-encodable are safe (arrays, integers, strings, booleans, etc).
     * Whatever this returns should be something [[normalizeValue()]] can handle.
     *
     * @param mixed $value The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     * @return mixed The serialized field value
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return $this->normalizeValue($value, $element)->getRelationships()->pluck('targetId')->all();
    }

    /**
     * Returns whether the given value should be considered “empty” to a validator.
     *
     * @param mixed $value The field’s value
     * @param ElementInterface $element The element the field is associated with, if there is one
     * @return bool Whether the value should be considered “empty”
     * @see Validator::$isEmpty
     */
    public function isValueEmpty($value, ElementInterface $element): bool
    {
        return $this->normalizeValue($value, $element)->getRelationships()->isEmpty();
    }

    /**
     * /**
     * Returns a static (non-editable) version of the field’s input HTML.
     *
     * This function is called to output field values when viewing entry drafts.
     *
     * @param mixed $value The field’s value
     * @param ElementInterface $element The element the field is associated with
     * @return string The static version of the field’s input HTML
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getStaticHtml($value, ElementInterface $element): string
    {
        $relationship = $this->normalizeValue($value, $element);

        $value = $relationship->getCollection()->all();

        if (empty($value)) {
            return '<p class="light">' . Craft::t('app', 'Nothing selected.') . '</p>';
        }

        $view = Craft::$app->getView();
        $id = $view->formatInputId($this->handle);
        $html = "<div id='{$id}' class='elementselect'><div class='elements'>";

        foreach ($value as $relatedElement) {
            $html .= Craft::$app->getView()->renderTemplate('_elements/element', [
                'element' => $relatedElement
            ]);
        }

        $html .= '</div></div>';

        $nsId = $view->namespaceInputId($id);
        $js = <<<JS
(new Craft.ElementThumbLoader()).load($('#{$nsId}'));
JS;
        $view->registerJs($js);

        return $html;
    }

    /**
     * Returns the HTML that should be shown for this field in Table View.
     *
     * @param mixed $value The field’s value
     * @param ElementInterface $element The element the field is associated with
     * @return string The HTML that should be shown for this field in Table View
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        $relationship = $this->normalizeValue($value, $element);

        if (!$element = $relationship->getCollection()->first()) {
            return '';
        }

        return Craft::$app->getView()->renderTemplate('_elements/element', [
            'element' => $element
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getSearchKeywords($value, ElementInterface $element): string
    {
        if ($this->ignoreSearchKeywords === true) {
            return '';
        }

        $relationship = $this->normalizeValue($value, $element);

        $titles = [];
        foreach ($relationship->getCollection()->all() as $relatedElement) {
            $titles[] = (string)$relatedElement;
        }

        return StringHelper::toString($titles, ' ');
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
        $this->sortable = ($sortable === true);
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
        /** @var ElementInterface|string $elementType */
        $elementType = $this->elementType();

        return Craft::$app->getView()->renderTemplate(
            'element-lists/_components/fieldtypes/settings',
            [
                'settingsTemplate' => $this->settingsTemplate,
                'field' => $this,
                'pluralElementType' => $elementType::pluralDisplayName(),
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
