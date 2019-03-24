<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\element\lists\fields;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\helpers\StringHelper;
use flipbox\craft\element\lists\records\Association;
use flipbox\craft\ember\helpers\SiteHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property int|null $id
 * @property int|null $limit
 * @property bool $allowLimit
 */
trait NormalizeValueTrait
{
    abstract protected static function elementType(): string;

    /**
     * @param $value
     * @param ElementInterface|null $element
     * @return ElementQuery
     */
    public function normalizeValue(
        $value,
        ElementInterface $element = null
    ) {
        if ($value instanceof ElementQuery) {
            return $value;
        }

        /** @var Element $elementType */
        $elementType = static::elementType();

        /** @var ElementQuery $query */
        $query = $elementType::find();

        if ($this->allowLimit && $this->limit) {
            $query->limit($this->limit);
        }

        $this->normalizeQueryValue($query, $value, $element);
        return $query;
    }

    /**
     * @param ElementQuery $query
     * @param ElementInterface|null $element
     */
    protected function normalizeQuery(
        ElementQuery $query,
        ElementInterface $element = null
    ) {
        /** @var Element|null $element */

        $source = ($element !== null && $element->getId() !== null ? $element->getId() : false);

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
                        $alias . '.fieldId' => $this->id,
                        $alias . '.siteId' => SiteHelper::ensureSiteId($element === null ? null : $element->siteId)
                    ]
                ]
            );
        } else {
            $query->id(false);
        }
    }

    /**
     * @param ElementQuery $query
     * @param $value
     * @param ElementInterface|null $element
     */
    protected function normalizeQueryValue(
        ElementQuery $query,
        $value,
        ElementInterface $element = null
    ) {
        $this->normalizeQuery($query, $element);

        if (is_array($value)) {
            $this->normalizeQueryInputValues($query, $value, $element);
            return;
        }

        if ($value === '') {
            $this->normalizeQueryEmptyValue($query);
            return;
        }
    }

    /**
     * @param ElementQuery $query
     * @param array $value
     * @param ElementInterface|null $element
     */
    protected function normalizeQueryInputValues(
        ElementQuery $query,
        array $value,
        ElementInterface $element = null
    ) {
        $models = [];
        $sortOrder = 1;
        foreach ($value as $val) {
            $models[] = $this->normalizeQueryInputValue($val, $sortOrder, $element);
        }
        $query->setCachedResult($models);
    }

    /**
     * @param $value
     * @param int $sortOrder
     * @param ElementInterface|Element|null $element
     * @return Association
     */
    protected function normalizeQueryInputValue(
        $value,
        int &$sortOrder,
        ElementInterface $element = null
    ): Association {

        if (is_array($value)) {
            $value = StringHelper::toString($value);
        }

        return new Association([
            'fieldId' => $this->id,
            'targetId' => $value,
            'sourceId' => $element === null ? null : $element->getId(),
            'siteId' => SiteHelper::ensureSiteId($element === null ? null : $element->siteId),
            'sortOrder' => $sortOrder++
        ]);
    }

    /**
     * @param ElementQuery $query
     */
    protected function normalizeQueryEmptyValue(
        ElementQuery $query
    ) {
        $query->setCachedResult([]);
    }
}
