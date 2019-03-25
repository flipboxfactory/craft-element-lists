<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\element\lists\fields;

use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Db;
use flipbox\craft\element\lists\records\Association;

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

        if (is_string($value)) {
            $this->modifyElementsQueryForStringValue($query, $value);
            return null;
        }

        $this->modifyElementsQueryForTargetValue($query, $value);
        return null;
    }

    /**
     * @param ElementQuery $query
     * @param string $value
     */
    protected function modifyElementsQueryForStringValue(
        ElementQuery $query,
        string $value
    ) {
        if ($value === 'not :empty:') {
            $value = ':notempty:';
        }

        if ($value === ':notempty:' || $value === ':empty:') {
            $this->modifyElementsQueryForEmptyValue($query, $value);
            return;
        }

        $this->modifyElementsQueryForTargetValue($query, $value);
    }

    /**
     * @param ElementQuery $query
     * @param $value
     */
    protected function modifyElementsQueryForTargetValue(
        ElementQuery $query,
        $value
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
            Db::parseParam($alias . '.sourceId', $value)
        );

        $query->query->distinct(true);
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
