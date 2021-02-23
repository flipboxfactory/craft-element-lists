<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\actions\source;

use craft\base\Field;
use flipbox\craft\ember\actions\records\ManageRecordTrait;
use flipbox\craft\element\lists\actions\ResolverTrait;
use flipbox\craft\element\lists\records\Association;
use yii\base\Action;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
class Associate extends Action
{
    use ManageRecordTrait,
        ResolverTrait;

    /**
     * @param string $field
     * @param string $source
     * @param string $target
     * @param int|null $siteId
     * @param int|null $sortOrder
     * @return mixed
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\web\HttpException
     */
    public function run(
        string $field,
        string $source,
        string $target,
        int $siteId = null,
        int $sortOrder = null
    ) {

        // Resolve
        $field = $this->resolveField($field);
        $source = $this->resolveElement($source);
        $target = $this->resolveElement($target);

        /** @var Field $field */

        $siteId = $this->resolveSiteId($siteId ?: $source->siteId);

        $query = Association::find()
            ->fieldId($field->id)
            ->sourceId($source->getId() ?: false)
            ->targetId($target->getId() ?: false);

        if ($siteId) {
            $query->siteId($siteId);
        }

        if (!$record = $query->one()) {
            $record = new Association([
                'fieldId' => $field->id,
                'sourceId' => $source->getId(),
                'targetId' => $target->getId(),
                'siteId' => $siteId
            ]);
        }

        if ($sortOrder) {
            $record->sortOrder = $sortOrder;
        }

        return $this->runInternal($record);
    }

    /**
     * @inheritdoc
     * @param Association $record
     * @throws \Exception
     */
    protected function performAction(ActiveRecord $record): bool
    {
        return $record->save();
    }
}
