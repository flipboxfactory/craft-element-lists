<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\db;

use Craft;
use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\craft\sortable\associations\db\SortableAssociationQuery;
use flipbox\craft\sortable\associations\db\traits\SiteAttribute;
use flipbox\element\lists\records\Association;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method Association[] getCachedResult()
 */
class SourceAssociationsQuery extends SortableAssociationQuery
{
    use traits\Attributes,
        SiteAttribute;

    /**
     * @inheritdoc
     */
    protected function fixedOrderColumn(): string
    {
        return 'targetId';
    }

    /**
     * @inheritdoc
     *
     * @throws QueryAbortedException if it can be determined that there won’t be any results
     */
    public function prepare($builder)
    {
        if (($this->fieldId !== null && empty($this->fieldId)) ||
            ($this->targetId !== null && empty($this->targetId))
        ) {
            throw new QueryAbortedException();
        }

        if ($this->siteId !== null) {
            $this->andWhere(Db::parseParam('siteId', $this->siteId));
        } else {
            $this->andWhere(Db::parseParam('siteId', Craft::$app->getSites()->currentSite->id));
        }

        $this->applySiteConditions();
        $this->applyConditions();

        return parent::prepare($builder);
    }
}
