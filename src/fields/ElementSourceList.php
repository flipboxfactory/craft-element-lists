<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\fields;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\fields\BaseRelationField;
use flipbox\craft\elements\nestedIndex\web\assets\index\NestedElementIndex;
use flipbox\element\lists\ElementList;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ElementSourceList extends BaseRelationField
{
    /**
     * The element class
     */
    const ELEMENT_CLASS = Element::class;

    /**
     * @var string Template to use for field rendering
     */
    protected $inputTemplate = 'element-lists/_components/fieldtypes/ElementSource';

    /**
     * @var string|null The JS class that should be initialized for the input
     */
    protected $inputJsClass = 'Craft.NestedElementIndexSelectInput';

    /**
     * @var string|null The JS class that should be initialized for the index
     */
    protected $indexJsClass = 'Craft.NestedElementIndex';

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return static::ELEMENT_CLASS;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('element-lists', 'Element List');
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return ElementList::getInstance()->getSourceFields()->normalizeValue(
            $this,
            $value,
            $element
        );
    }

    /**
     * @inheritdoc
     */
    public function modifyElementsQuery(ElementQueryInterface $query, $value)
    {
        return ElementList::getInstance()->getSourceFields()->modifyElementsQuery(
            $this,
            $query,
            $value
        );
    }

    /**
     * @param null $value
     * @param ElementInterface|null $element
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function inputTemplateVariables($value = null, ElementInterface $element = null): array
    {
        Craft::$app->getView()->registerAssetBundle(NestedElementIndex::class);

        if ($value instanceof ElementQuery) {
            $value = $value->status(null)
                ->enabledForSite(false)
                ->all();
        } else {
            if (!is_array($value)) {
                $value = [];
            }
        }

        $fieldService = ElementList::getInstance()->getSourceFields();

        return [
            'element' => $element,
            'value' => $value,
            'container' => 'nested-index-' . $this->handle,
            'elementType' => static::elementType(),
            'inputJsClass' => $this->inputJsClass,
            'inputJs' => $fieldService->getInputJs($this, $element),
            'indexJsClass' => $this->indexJsClass,
            'indexJs' => $fieldService->getIndexJs($this, $element)
        ];
    }
}
