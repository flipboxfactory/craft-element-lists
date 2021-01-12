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
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ArrayHelper;
use flipbox\craft\element\lists\relationships\RelationshipInterface;
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
     * @inheritDoc
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\InvalidConfigException
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        /** @var Element|null $element */
        if ($element !== null && $element->hasEagerLoadedElements($this->handle)) {
            $value = $element->getEagerLoadedElements($this->handle);
        }

        /** @var ElementQuery|array $value */
        $variables = $this->inputTemplateVariables($value, $element);

        return Craft::$app->getView()->renderTemplate(
            'element-lists/_components/fieldtypes/input',
            array_merge(
                [
                    'inputTemplate' => $this->inputTemplate
                ],
                $variables
            )
        );
    }

    /**
     * @param RelationshipInterface|null $value
     * @param ElementInterface|null $element
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function inputTemplateVariables($value = null, ElementInterface $element = null): array
    {
        if ($value instanceof RelationshipInterface) {
            $value = $value->getCollection()->pluck('id')->all();
        }

        if (!is_array($value)) {
            $value = [];
        }

        Craft::$app->getView()->registerAssetBundle(NestedElementIndex::class);

        $sortOptions = [];
        if ($this->context === 'global') {
            $sortOptions['field'] = ElementList::t('Field Order');
        }

        return [
            'sources' => $this->inputToIndexSources(
                $this->inputSources($element)
            ),
            'element' => $element,
            'container' => 'nested-index-' . $this->handle,
            'fieldSortOptions' => $sortOptions,
            'elementType' => static::elementType(),
            'inputJsClass' => 'Craft.NestedElementIndexSelectInput',
            'inputJs' => $this->getInputJs($value, $element),
            'indexJsClass' => 'Craft.NestedElementIndex',
            'indexJs' => $this->getIndexJs($element)
        ];
    }

    /**
     * Converts input sources to index sources (used to filter results).
     *
     * @param $sources
     * @return array
     */
    protected function inputToIndexSources($sources): array
    {
        $indexSources = Craft::$app->getElementIndexes()->getSources(static::elementType());

        if ($sources === '*') {
            // Remove any structure sources
            foreach ($indexSources as &$indexSource) {
                ArrayHelper::remove($indexSource, 'structureEditable');
                ArrayHelper::remove($indexSource, 'structureId');
            }

            return $indexSources;
        }

        if (!is_array($sources)) {
            $sources = [$sources];
        }

        // Only return the selected sources
        foreach ($indexSources as $key => $indexSource) {
            if (!array_key_exists('key', $indexSource)) {
                unset($indexSources[$key]);
                continue;
            }

            if (!in_array($indexSource['key'], $sources)) {
                unset($indexSources[$key]);
            }
        }

        return $indexSources;
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
                'field' => $this->id,
                'site' => $siteId,
            ],
            'modalSettings' => [
                'sendDisabledModalElementsAsString' => true,
                'indexSettings' => [
                    'updateElementsAction' => 'element-lists/element-indexes/get-elements',
                    'submitActionsAction' => 'element-lists/element-indexes/perform-action',
                    'loadMoreElementsAction' => 'element-lists/element-indexes/get-more-elements'
                ]
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

        $siteId = SiteHelper::ensureSiteId($element ? $element->siteId : null);

        return [
            'source' => 'nested',
            'context' => 'index',
            'viewMode' => $this->viewMode,
            'showStatusMenu' => true,
            'showSiteMenu' => true,
            'hideSidebar' => true,
            'toolbarFixed' => false,
            'storageKey' => 'nested.index.' . $this->handle,
            'updateElementsAction' => 'element-lists/element-indexes/get-elements',
            'submitActionsAction' => 'element-lists/element-indexes/perform-action',
            'loadMoreElementsAction' => 'element-lists/element-indexes/get-more-elements',
            'criteria' => [
                'enabledForSite' => null,
                'siteId' => $siteId,
                'relatedTo' => [
                    'sourceElement' => $elementId,
                    'field' => $this->id,
                    'sourceSite' => $siteId
                ]
            ],
            'viewParams' => [
                'sourceId' => $elementId,
                'fieldId' => $this->id
            ]
        ];
    }
}
