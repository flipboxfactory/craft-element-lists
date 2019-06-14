<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\relationships;

use craft\base\ElementInterface;
use craft\elements\db\ElementQueryInterface;
use flipbox\craft\element\lists\records\Association;
use Tightenco\Collect\Support\Collection;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
interface RelationshipInterface
{

    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @return ElementQueryInterface
     */
    public function getQuery(): ElementQueryInterface;

    /************************************************************
     * COLLECTIONS
     ************************************************************/

    /**
     * @return Collection|ElementInterface[]
     */
    public function getElements(): Collection;

    /**
     * @return Collection|Association[]
     */
    public function getCollection(): Collection;


    /************************************************************
     * ADD / REMOVE
     ************************************************************/

    /**
     * Add one or many object relations (but do not save)
     *
     * @param string|int|string[]|int[]|ElementInterface|QueryInterface|Collection|ElementInterface[] $objects
     * @param array $attributes
     * @return static
     */
    public function add($objects, array $attributes = []): RelationshipInterface;

    /**
     * Remove one or many object relations (but do not save)
     *
     * @param string|int|string[]|int[]|ElementInterface|QueryInterface|Collection|ElementInterface[] $objects
     * @return static
     */
    public function remove($objects): RelationshipInterface;


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * Save the current collection of relationships.  This should update existing relationships, create
     * new relationships and delete abandoned relationships.
     *
     * @return bool
     */
    public function save(): bool;


    /************************************************************
     * UTILS
     ************************************************************/

    /**
     * Check if a relationship already exists
     *
     * @param string|int|ElementInterface $object
     * @return bool
     */
    public function exists($object): bool;

    /**
     * Check if the relationships have been altered
     *
     * @return bool
     */
    public function isMutated(): bool;

    /**
     * Reset relationships to their original state
     */
    public function reset(): RelationshipInterface;
}