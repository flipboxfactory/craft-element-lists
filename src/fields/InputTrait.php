<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\element\lists\fields;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ArrayHelper;
use flipbox\craft\elements\nestedIndex\web\assets\index\NestedElementIndex;
use flipbox\craft\ember\helpers\SiteHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property string $inputTemplate
 * @property string $inputJsClass
 * @property string $handle
 * @property int $id
 * @property string|null $viewMode
 * @property int|null $limit
 * @property string $selectionLabel
 */
trait InputTrait
{
    /**
     * Returns the element class associated with this field type.
     *
     * @return string The Element class name
     */
    abstract protected static function elementType(): string;

    /**
     * Returns an array of the source keys the field should be able to select elements from.
     *
     * @param ElementInterface|null $element
     * @return array|string
     */
    abstract protected function inputSources(ElementInterface $element = null);

    /**
     * @param null $value
     * @param ElementInterface|null $element
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function inputTemplateVariables($value = null, ElementInterface $element = null): array
    {
        if ($value instanceof ElementQueryInterface) {
            $value = $value
                ->anyStatus()
                ->ids();
        } elseif (!is_array($value)) {
            $value = [];
        }

        Craft::$app->getView()->registerAssetBundle(NestedElementIndex::class);

        $sources = Craft::$app->getElementIndexes()->getSources(static::elementType());
        foreach ($sources as &$source) {
            ArrayHelper::remove($source, 'structureEditable');
            ArrayHelper::remove($source, 'structureId');
        }

        return [
            'sources' => $sources,
            'element' => $element,
            'container' => 'nested-index-' . $this->handle,
            'elementType' => static::elementType(),
            'inputJsClass' => $this->inputJsClass,
            'inputJs' => $this->getInputJs($value, $element),
            'indexJsClass' => 'Craft.NestedElementIndex',
            'indexJs' => $this->getIndexJs($element)
        ];
    }

    /*******************************************
     * JS CONFIGS
     *******************************************/

    /**
     * @param array $elementIds
     * @param ElementInterface $element
     * @return array
     */
    private function getInputJs(array $elementIds, ElementInterface $element = null): array
    {
        /** @var Element $element */
        $siteId = SiteHelper::ensureSiteId($element ? $element->siteId : null);

        $selectionCriteria = [
            'enabledForSite' => null,
            'siteId' => $siteId
        ];

        return [
            'elementType' => static::elementType(),
            'sources' => $this->inputSources($element),
            'criteria' => $selectionCriteria,
            'sourceElementId' => $element->getId() ?: null,
            'viewMode' => $this->viewMode,
            'limit' => $this->limit,
            'selectionLabel' => $this->selectionLabel,
            'storageKey' => 'nested.index.input.' . $this->handle,
            'elements' => $elementIds,
            'addAction' => 'element-lists/source/associate',
            'selectTargetAttribute' => 'target',
            'selectParams' => [
                'source' => $element->getId() ?: null,
                'field' => $this->id
            ]
        ];
    }

    /**
     * @param ElementInterface $element
     * @return array
     */
    private function getIndexJs(ElementInterface $element = null): array
    {

        /** @var Element $element */
        $elementId = ($element !== null && $element->getId() !== null) ? $element->getId() : false;

        return [
            'source' => 'nested',
            'context' => 'index',
            'viewMode' => $this->viewMode,
            'showStatusMenu' => true,
            'showSiteMenu' => true,
            'hideSidebar' => false,
            'toolbarFixed' => false,
            'storageKey' => 'nested.index.' . $this->handle,
            'updateElementsAction' => 'element-lists/element-indexes/get-elements',
            'submitActionsAction' => 'element-lists/element-indexes/perform-action',
            'loadMoreElementsAction' => 'element-lists/element-indexes/get-more-elements',
            'criteria' => [
                'enabledForSite' => null,
                'siteId' => SiteHelper::ensureSiteId($element ? $element->siteId : null),
                $this->handle => $elementId
            ],
            'viewParams' => [
                'sourceId' => $elementId,
                'fieldId' => $this->id
            ]
        ];
    }
}
