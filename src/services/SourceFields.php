<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\services;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\elements\db\ElementQuery;
use craft\helpers\StringHelper;
use flipbox\craft\sortable\associations\db\SortableAssociationQueryInterface;
use flipbox\craft\sortable\associations\records\SortableAssociationInterface;
use flipbox\craft\sortable\associations\services\SortableFields;
use flipbox\element\lists\db\SourceElementQuery;
use flipbox\element\lists\ElementList;
use flipbox\element\lists\fields\ElementSourceList;
use flipbox\element\lists\records\Association;
use flipbox\ember\helpers\SiteHelper;
use yii\base\Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SourceFields extends SortableFields
{
    /**
     * @inheritdoc
     */
    const SOURCE_ATTRIBUTE = Association::TARGET_ATTRIBUTE;

    /**
     * @inheritdoc
     */
    const TARGET_ATTRIBUTE = Association::SOURCE_ATTRIBUTE;

    /**
     * @inheritdoc
     */
    protected static function tableAlias(): string
    {
        return Association::TABLE_ALIAS;
    }

    /**
     * @param FieldInterface $field
     * @throws Exception
     */
    private function ensureField(FieldInterface $field)
    {
        if (!$field instanceof ElementSourceList) {
            throw new Exception(sprintf(
                "The field must be an instance of '%s', '%s' given.",
                (string)ElementSourceList::class,
                (string)get_class($field)
            ));
        }
    }

    /**
     * @inheritdoc
     */
    public function getQuery(
        FieldInterface $field,
        ElementInterface $element = null
    ): SortableAssociationQueryInterface {
        /** @var ElementSourceList $field */
        $this->ensureField($field);

        $query = new SourceElementQuery($field::ELEMENT_CLASS);

        if ($field->allowLimit === true && $field->limit !== null) {
            $query->limit($field->limit);
        }

        return $query;
    }
    /*******************************************
     * NORMALIZE VALUE
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function normalizeQuery(
        FieldInterface $field,
        SortableAssociationQueryInterface $query,
        ElementInterface $element = null
    ) {
        /** @var ElementSourceList $field */
        /** @var ElementQuery $query */
        /** @var Element $element */
        $this->ensureField($field);

        $source = $this->resolveElementId($element);

        if ($source !== false) {
            $name = Association::tableName();
            $alias = Association::tableAlias();

            $query->innerJoin(
                $name . ' ' . $alias,
                [
                    'and',
                    '[[' . $alias . '.targetId]] = [[elements.id]]',
                    [
                        $alias . '.sourceId' => $source,
                        $alias . '.fieldId' => $field->id,
                        $alias . '.siteId' => SiteHelper::ensureSiteId($element ? $element->siteId : null)
                    ]
                ]
            );
        } else {
            $query->id(false);
        }
    }

    /**
     * @inheritdoc
     */
    protected function normalizeQueryInputValue(
        FieldInterface $field,
        $value,
        int &$sortOrder,
        ElementInterface $element = null
    ): SortableAssociationInterface {
        /** @var ElementSourceList $field */
        $this->ensureField($field);

        if (is_array($value)) {
            $value = StringHelper::toString($value);
        }

        return new Association(
            [
                'fieldId' => $field->id,
                'targetId' => $value,
                'sourceId' => $element ? $element->getId() : null,
                'siteId' => $this->targetSiteId($element),
                'sortOrder' => $sortOrder++
            ]
        );
    }

    /**
     * @param ElementSourceList $field
     * @param ElementInterface $element
     * @return array
     */
    public function getInputJs(
        ElementSourceList $field,
        ElementInterface $element
    ): array {
        /** @var Element $element */
        $siteId = SiteHelper::ensureSiteId($element->siteId);

        $selectionCriteria = [
            'enabledForSite' => null,
            'siteId' => $siteId
        ];

        return [
            'elementType' => $field::ELEMENT_CLASS,
            'sources' => $this->inputSources($field),
            'criteria' => $selectionCriteria,
            'viewMode' => 'list',
            'limit' => $field->limit,
            'selectionLabel' => $field->selectionLabel,
            'storageKey' => 'nested.index.input.' . $field->handle,
            'elements' => $this->associatedElementIds($element, $siteId),
            'addAction' => 'element-lists/source/associate',
            'selectParams' => [
                'source' => $element->getId() ?: null,
                'field' => $field->id
            ]
        ];
    }

    /**
     * @param ElementSourceList $field
     * @param ElementInterface $element
     * @return array
     */
    public function getIndexJs(
        ElementSourceList $field,
        ElementInterface $element
    ): array {

        /** @var Element $element */
        $elementId = $this->resolveElementId($element);

        return [
            'source' => 'nested',
            'context' => 'index',
            'storageKey' => 'nested.index.' . $field->handle,
            'toolbarFixed' => false,
            'updateElementsAction' => 'element-lists/element-indexes/get-elements',
            'submitActionsAction' => 'element-lists/element-indexes/perform-action',
            'criteria' => [
                'enabledForSite' => null,
                'siteId' => SiteHelper::ensureSiteId($element->siteId),
                $field->handle => $elementId
            ],
            'viewParams' => [
                'sourceId' => $elementId,
                'fieldId' => $field->id
            ]
        ];
    }

    /**
     * @param ElementInterface|null $element
     * @return bool|int
     */
    private function resolveElementId(
        ElementInterface $element = null
    ) {
        return $element !== null && $element->getId() !== null ? $element->getId() : false;
    }

    /**
     * @param ElementSourceList $field
     * @return array|null|string|string[]
     */
    private function inputSources(
        ElementSourceList $field
    ) {
        if ($field->allowMultipleSources) {
            $sources = $field->sources;
        } else {
            $sources = [$field->source];
        }

        return $sources;
    }

    /**
     * @param ElementInterface $element
     * @param int $siteId
     * @return array
     */
    private function associatedElementIds(ElementInterface $element, int $siteId)
    {
        return ElementList::getInstance()->getSourceAssociations()->getQuery([
            'sourceId' => $element->getId(),
            'siteId' => $siteId
        ])
            ->select('targetId')
            ->column();
    }
}
