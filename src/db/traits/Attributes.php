<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\db\traits;

use craft\helpers\Db;
use yii\base\Exception;
use yii\db\Expression;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait Attributes
{
    /**
     * @var int|int[]|false|null The source Id(s). Prefix Ids with "not " to exclude them.
     */
    public $sourceId;

    /**
     * @var int|int[]|false|null The target Id(s). Prefix Ids with "not " to exclude them.
     */
    public $targetId;

    /**
     * @var int|int[]|false|null The field ID(s). Prefix IDs with "not " to exclude them.
     */
    public $fieldId;

    /**
     * Adds an additional WHERE condition to the existing one.
     * The new condition and the existing one will be joined using the `AND` operator.
     * @param string|array|Expression $condition the new WHERE condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return $this the query object itself
     * @see where()
     * @see orWhere()
     */
    abstract public function andWhere($condition, $params = []);

    /**
     * @param $value
     * @return static
     */
    public function fieldId($value)
    {
        $this->fieldId = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static
     */
    public function field($value)
    {
        return $this->fieldId($value);
    }

    /**
     * @inheritdoc
     * @throws Exception if $value is an invalid site handle
     * return static
     */
    public function target($value)
    {
        return $this->targetId($value);
    }

    /**
     * @inheritdoc
     * return static
     */
    public function targetId($value)
    {
        $this->targetId = $value;
        return $this;
    }

    /**
     * @inheritdoc
     * return static
     */
    public function source($value)
    {
        return $this->sourceId($value);
    }

    /**
     * @inheritdoc
     * return static
     */
    public function sourceId($value)
    {
        $this->sourceId = $value;
        return $this;
    }

    /**
     * Apply conditions
     */
    protected function applyConditions()
    {
        if ($this->fieldId !== null) {
            $this->andWhere(Db::parseParam('fieldId', $this->fieldId));
        }

        if ($this->sourceId !== null) {
            $this->andWhere(Db::parseParam('sourceId', $this->sourceId));
        }

        if ($this->targetId !== null) {
            $this->andWhere(Db::parseParam('targetId', $this->targetId));
        }
    }
}
