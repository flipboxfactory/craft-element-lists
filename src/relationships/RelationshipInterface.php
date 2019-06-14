<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\relationships;

use craft\base\ElementInterface;
use flipbox\craft\element\lists\records\Association;
use Tightenco\Collect\Support\Collection;
use yii\base\Exception;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
interface RelationshipInterface extends \Countable
{

    /************************************************************
     * FIND
     ************************************************************/

    /**
     * Find a relationship
     *
     * @param Association|ElementInterface|int|string|null $object
     * @return Association|null
     */
    public function findOne($object = null);

    /**
     * Find a relationship or create a new one
     *
     * @param Association|ElementInterface|int|string $object
     * @return Association
     */
    public function findOrCreate($object): Association;

    /**
     * Find a relationship or throw an exception if not found
     *
     * @param Association|ElementInterface|int|string $object
     * @return Association
     * @throws Exception
     */
    public function findOrFail($object): Association;


    /************************************************************
     * COLLECTIONS
     ************************************************************/

    /**
     * A collection of related elements/objects.
     *
     * @return Collection|ElementInterface[]
     */
    public function getCollection(): Collection;

    /**
     * A collection of relationships.
     *
     * @return Collection|Association[]
     */
    public function getRelationships(): Collection;


    /************************************************************
     * ADD / REMOVE
     ************************************************************/

    /**
     * Add one or many relations (but do not save)
     *
     * @param string|int|string[]|int[]|ElementInterface|QueryInterface|Collection|ElementInterface[] $objects
     * @param array $attributes
     * @return static
     */
    public function add($objects, array $attributes = []): RelationshipInterface;

    /**
     * Remove one or many relations (but do not save)
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
     * Check if relationships have been altered
     *
     * @return bool
     */
    public function isMutated(): bool;

    /**
     * Clears all current relationships
     *
     * @return RelationshipInterface
     */
    public function clear(): RelationshipInterface;

    /**
     * Reset relationships to their original state
     *
     * @return RelationshipInterface
     */
    public function reset(): RelationshipInterface;
}
