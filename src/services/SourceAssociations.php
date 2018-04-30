<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\services;

use flipbox\craft\sortable\associations\db\SortableAssociationQueryInterface;
use flipbox\craft\sortable\associations\records\SortableAssociationInterface;
use flipbox\craft\sortable\associations\services\SortableAssociations;
use flipbox\element\lists\db\SourceAssociationsQuery;
use flipbox\element\lists\records\Association;
use yii\db\ActiveQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SourceAssociations extends SortableAssociations
{
    /**
     * @inheritdoc
     */
    const SOURCE_ATTRIBUTE = Association::SOURCE_ATTRIBUTE;

    /**
     * @inheritdoc
     */
    const TARGET_ATTRIBUTE = Association::TARGET_ATTRIBUTE;

    /**
     * @inheritdoc
     */
    protected static function tableAlias(): string
    {
        return Association::TABLE_ALIAS;
    }

    /**
     * @inheritdoc
     * @return SourceAssociationsQuery
     */
    public function getQuery($config = []): SortableAssociationQueryInterface
    {
        return new SourceAssociationsQuery(Association::class, $config);
    }

    /**
     * @inheritdoc
     * @param Association $record
     * @return SourceAssociationsQuery
     */
    protected function associationQuery(
        SortableAssociationInterface $record
    ): SortableAssociationQueryInterface {
        return $this->query(
            $record->{static::SOURCE_ATTRIBUTE},
            $record->fieldId,
            $record->siteId
        );
    }

    /**
     * @inheritdoc
     * @param SourceAssociationsQuery $query
     */
    protected function existingAssociations(
        SortableAssociationQueryInterface $query
    ): array {
        $source = $this->resolveStringAttribute($query, static::SOURCE_ATTRIBUTE);
        $field = $this->resolveStringAttribute($query, 'fieldId');
        $site = $this->resolveStringAttribute($query, 'siteId');

        if ($source === null || $field === null || $site === null) {
            return [];
        }

        return $this->associations($source, $field, $site);
    }

    /**
     * @param $source
     * @param int $fieldId
     * @param int $siteId
     * @return SourceAssociationsQuery
     */
    private function query(
        $source,
        int $fieldId,
        int $siteId
    ): SortableAssociationQueryInterface {
        return $this->getQuery()
            ->where([
                static::SOURCE_ATTRIBUTE => $source,
                'fieldId' => $fieldId,
                'siteId' => $siteId
            ])
            ->orderBy(['sortOrder' => SORT_ASC]);
    }

    /**
     * @param $source
     * @param int $fieldId
     * @param int $siteId
     * @return array
     */
    private function associations(
        $source,
        int $fieldId,
        int $siteId
    ): array {
        return $this->query($source, $fieldId, $siteId)
            ->indexBy(static::TARGET_ATTRIBUTE)
            ->all();
    }
}
