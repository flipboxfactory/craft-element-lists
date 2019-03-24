<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\craft\element\lists\queries;

use Craft;
use craft\base\ElementInterface;
use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\ember\queries\AuditAttributesTrait;
use flipbox\craft\ember\queries\CacheableActiveQuery;
use flipbox\craft\ember\queries\FieldAttributeTrait;
use flipbox\craft\ember\queries\SiteAttributeTrait;
use flipbox\craft\integration\records\IntegrationAssociation;
use yii\db\Query;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @method IntegrationAssociation[] getCachedResult()
 * @method IntegrationAssociation[] all()
 * @method IntegrationAssociation one()
 */
class AssociationQuery extends CacheableActiveQuery
{
    use AuditAttributesTrait,
        FieldAttributeTrait,
        SiteAttributeTrait;

    /**
     * @var int|null Sort order
     */
    public $sortOrder;

    /**
     * @var string|string[]|null
     */
    public $target;

    /**
     * @var string|string[]|null
     */
    public $source;

    /**
     * @param $value
     * @return $this
     */
    public function sortOrder($value)
    {
        $this->sortOrder = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSortOrder($value)
    {
        return $this->sortOrder($value);
    }

    /**
     * @param int|int[]|string|string[]|ElementInterface|ElementInterface[]|false|null $value
     * @return static
     */
    public function setTargetId($value)
    {
        return $this->setTarget($value);
    }

    /**
     * @param int|int[]|string|string[]|ElementInterface|ElementInterface[]|false|null $value
     * @return static
     */
    public function targetId($value)
    {
        return $this->setTarget($value);
    }

    /**
     * @param int|int[]|string|string[]|ElementInterface|ElementInterface[]|false|null $value
     * @return static
     */
    public function setTarget($value)
    {
        $this->target = $value;
        return $this;
    }

    /**
     * @param int|int[]|string|string[]|ElementInterface|ElementInterface[]|false|null $value
     * @return static
     */
    public function target($value)
    {
        return $this->setTarget($value);
    }


    /**
     * @param int|int[]|string|string[]|ElementInterface|ElementInterface[]|false|null $value
     * @return static
     */
    public function setSourceId($value)
    {
        return $this->setSource($value);
    }

    /**
     * @param int|int[]|string|string[]|ElementInterface|ElementInterface[]|false|null $value
     * @return static
     */
    public function sourceId($value)
    {
        return $this->setSource($value);
    }

    /**
     * @param int|int[]|string|string[]|ElementInterface|ElementInterface[]|false|null $value
     * @return static
     */
    public function setSource($value)
    {
        $this->source = $value;
        return $this;
    }

    /**
     * @param int|int[]|string|string[]|ElementInterface|ElementInterface[]|false|null $value
     * @return static
     */
    public function source($value)
    {
        return $this->setSource($value);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->select === null) {
            $this->select = ['*'];
        }

        if ($this->orderBy === null) {
            $this->orderBy = ['sortOrder' => SORT_ASC];
        }
    }

    /**
     * @inheritdoc
     * @throws QueryAbortedException
     */
    public function prepare($builder)
    {
        // Is the query already doomed?
        if (($this->field !== null && empty($this->field)) ||
            ($this->target !== null && empty($this->target)) ||
            ($this->source !== null && empty($this->source))
        ) {
            throw new QueryAbortedException();
        }

        $this->applyFieldConditions();
        $this->applySiteConditions();
        $this->applyAuditAttributeConditions();

        if ($this->sortOrder !== null) {
            $this->andWhere(Db::parseParam('sortOrder', $this->sortOrder));
        }

        if ($this->target !== null) {
            $this->andWhere(Db::parseParam('targetId', $this->parseElementValue($this->target)));
        }

        if ($this->source !== null) {
            $this->andWhere(Db::parseParam('sourceId', $this->parseElementValue($this->source)));
        }

        return parent::prepare($builder);
    }

    /**
     * Apply attribute conditions
     */
    protected function applyFieldConditions()
    {
        if ($this->field !== null) {
            $this->andWhere(Db::parseParam('fieldId', $this->parseFieldValue($this->field)));
        }
    }

    /**
     * Apply attribute conditions
     */
    protected function applySiteConditions()
    {
        if ($this->site !== null) {
            $this->andWhere(Db::parseParam('siteId', $this->parseSiteValue($this->site)));
        } else {
            $this->andWhere(Db::parseParam('siteId', Craft::$app->getSites()->currentSite->id));
        }
    }

    /**
     * @param $value
     * @return array|string
     */
    protected function parseElementValue($value)
    {
        return QueryHelper::prepareParam(
            $value,
            function (string $uri) {
                $value = (new Query())
                    ->select(['id'])
                    ->from(['{{%elements_sites}} elements_sites'])
                    ->where(['uri' => $uri])
                    ->scalar();
                return empty($value) ? false : $value;
            }
        );
    }
}
