<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\fields;

use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Db;
use flipbox\craft\element\lists\records\Association;
use flipbox\craft\ember\helpers\ArrayHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property int|null id
 */
trait ModifyElementQueryTrait
{
    /**
     * @inheritdoc
     */
    public function modifyElementsQuery(ElementQueryInterface $query, $value)
    {
        if ($value === null || !$query instanceof ElementQuery) {
            return null;
        }

        if ($value === false) {
            return false;
        }

        if (is_array($value)) {
            $this->modifyElementsQueryForArrayValue($query, $value);
            return null;
        }

        if (is_string($value)) {
            $this->modifyElementsQueryForStringValue($query, $value);
            return null;
        }

        $this->modifyElementsQueryForTargetValue($query, $value);
        return null;
    }

    /**
     * @param ElementQuery $query
     * @param array $value
     * @return void
     */
    protected function modifyElementsQueryForArrayValue(
        ElementQuery $query,
        array $value
    ) {
        if (array_key_exists('source', $value)) {
            $this->modifyElementsQueryForStringValue(
                $query,
                $value['source'] ?: ':empty:',
                ArrayHelper::remove($value, 'sourceSiteId')
            );
            return null;
        }

        $this->modifyElementsQueryForTargetValue($query, $value);
    }

    /**
     * @param ElementQuery $query
     * @param string $value
     * @param $siteId
     */
    protected function modifyElementsQueryForStringValue(
        ElementQuery $query,
        string $value,
        $siteId = null
    ) {
        if ($value === 'not :empty:') {
            $value = ':notempty:';
        }

        if ($value === ':notempty:' || $value === ':empty:') {
            $this->modifyElementsQueryForEmptyValue($query, $value);
            return;
        }

        $this->modifyElementsQueryForTargetValue($query, $value, $siteId);
    }

    /**
     * @param ElementQuery $query
     * @param $sourceId
     * @param $siteId
     */
    protected function modifyElementsQueryForTargetValue(
        ElementQuery $query,
        $sourceId,
        $siteId = null
    ) {
        $alias = Association::tableAlias();
        $name = Association::tableName();

        $joinTable = "{$name} {$alias}";
        $query->query->innerJoin($joinTable, "[[{$alias}.targetId]] = [[subquery.elementsId]]");
        $query->subQuery->innerJoin($joinTable, "[[{$alias}.targetId]] = [[elements.id]]");

        $query->subQuery->addSelect(["{$alias}.sortOrder"]);

        $query->subQuery->andWhere(
            Db::parseParam($alias . '.fieldId', $this->id)
        );

        $query->subQuery->andWhere(
            Db::parseParam($alias . '.sourceId', $sourceId)
        );

        if ($this->localizeRelations && $siteId) {
            $query->subQuery->andWhere(
                Db::parseParam($alias . '.sourceSiteId', $siteId)
            );
        }
    }

    /**
     * @param ElementQuery $query
     * @param string $value
     */
    protected function modifyElementsQueryForEmptyValue(
        ElementQuery $query,
        string $value
    ) {
        $alias = Association::tableAlias();
        $name = Association::tableName();

        $operator = ($value === ':notempty:' ? '!=' : '=');
        $query->subQuery->andWhere(
            "(select count([[{$alias}.targetId]]) from " .
            $name .
            " {{{$alias}}} where [[{$alias}.targetId" .
            "]] = [[elements.id]] and [[{$alias}.fieldId]] = {$this->id}) {$operator} 0"
        );
    }
}
