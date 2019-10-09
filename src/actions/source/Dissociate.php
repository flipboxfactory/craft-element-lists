<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\actions\source;

use craft\base\Field;
use flipbox\craft\ember\actions\ManageTrait;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\craft\element\lists\actions\ResolverTrait;
use flipbox\craft\element\lists\records\Association;
use yii\base\Action;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
class Dissociate extends Action
{
    use ManageTrait,
        ResolverTrait;

    /**
     * @var int
     */
    public $statusCodeSuccess = 201;

    /**
     * @param string $field
     * @param string $source
     * @param string $target
     * @param int|null $siteId
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function run(
        string $field,
        string $source,
        string $target,
        int $siteId = null
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
            return true;
        }

        return $this->runInternal($record);
    }

    /**
     * @param Association $record
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function performAction(Association $record): bool
    {
        return $record->delete();
    }
}
