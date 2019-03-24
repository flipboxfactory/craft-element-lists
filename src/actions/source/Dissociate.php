<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\craft\element\lists\actions\source;

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

        $siteId = SiteHelper::ensureSiteId($siteId ?: $source->siteId);

        $record = Association::find()
            ->fieldId($field->id)
            ->sourceId($source->getId() ?: false)
            ->targetId($target->getId() ?: false)
            ->siteId($siteId)
            ->one();

        if (!$record) {
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
