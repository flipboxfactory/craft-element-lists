<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\relationships;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\db\ElementQuery;
use craft\helpers\ArrayHelper;
use flipbox\craft\element\lists\fields\RelationalInterface;
use flipbox\craft\element\lists\queries\AssociationQuery;
use flipbox\craft\element\lists\records\Association;
use flipbox\organizations\records\UserAssociation;
use Tightenco\Collect\Support\Collection;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\base\UnknownPropertyException;
use yii\db\QueryInterface;

/**
 * Manages User Types associated to Organization/User associations
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
class Relationship extends BaseObject implements RelationshipInterface
{
    /**
     * The element the relations are related to
     *
     * @var ElementInterface|null
     */
    private $element;

    /**
     * The field which accesses the relations
     *
     * @var RelationalInterface|Field
     */
    private $field;

    /**
     * @var Collection|null
     */
    private $relations;

    /**
     * @var bool
     */
    protected $mutated = false;

    /**
     * @param ElementInterface|null $element
     * @param RelationalInterface $field
     * @param array $config
     */
    public function __construct(RelationalInterface $field, ElementInterface $element = null, array $config = [])
    {
        $this->element = $element;
        $this->field = $field;

        parent::__construct($config);
    }


    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @param Association|ElementInterface|int|string $object
     * @return Association
     */
    public function findOrCreate($object): Association
    {
        if (null === ($association = $this->findOne($object))) {
            $association = $this->create($object);
        }

        return $association;
    }

    /**
     * @param Association|ElementInterface|int|string $object
     * @return Association
     * @throws Exception
     */
    public function findOrFail($object): Association
    {
        if (null === ($association = $this->findOne($object))) {
            throw new Exception("Relationship could not be found.");
        }

        return $association;
    }

    /**
     * @param Association|ElementInterface|int|string|null $object
     * @return Association|null
     */
    public function findOne($object = null)
    {
        if (null === ($key = $this->findKey($object))) {
            return null;
        }

        return $this->getRelationships()->get($key);
    }

    /************************************************************
     * COLLECTIONS
     ************************************************************/

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        if (null === $this->relations) {
            return Collection::make(
                $this->field->getQuery($this->element)
                    ->anyStatus()
                    ->all()
            );
        };

        return new Collection(
            $this->field->getQuery($this->element)
                ->id($this->getRelationships()->pluck('targetId')->all())
                ->fixedOrder(true)
                ->anyStatus()
                ->all()
        );
    }

    /**
     * @inheritDoc
     */
    public function getRelationships(): Collection
    {
        if (null === $this->relations) {
            $this->relations = $this->existingRelationships();
        }

        return $this->relations;
    }
    
    /**
     * @return Collection
     */
    protected function existingRelationships()
    {
        return $this->createRelations(
            $this->associationQuery()->all()
        );
    }

    /************************************************************
     * ADD / REMOVE
     ************************************************************/

    /**
     * @inheritDoc
     */
    public function add($objects, array $attributes = []): RelationshipInterface
    {
        foreach ($this->objectArray($objects) as $object) {
            $this->addOne($object, $attributes);
        }

        return $this;
    }

    /**
     * @param $object
     * @param array $attributes
     * @return RelationshipInterface
     */
    protected function addOne($object, array $attributes = []): RelationshipInterface
    {
        $isNew = false;

        // Check if it's already linked
        if (null === ($association = $this->findOne($object))) {
            $association = $this->create($object);
            $isNew = true;
        }

        // Modify?
        if (!empty($attributes)) {
            Craft::configure(
                $association,
                $attributes
            );

            $this->mutated = true;

            if (!$isNew) {
                $this->updateCollection($this->relations, $association);
            }
        }

        if ($isNew) {
            $this->addToRelations($association);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function remove($objects): RelationshipInterface
    {
        foreach ($this->objectArray($objects) as $object) {
            if (null !== ($key = $this->findKey($object))) {
                $this->removeFromRelations($key);
            }
        }

        return $this;
    }


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * @return bool
     */
    public function save(): bool
    {
        // No changes?
        if (!$this->isMutated()) {
            return true;
        }

        $success = true;

        list($save, $delete) = $this->delta();

        // Delete those removed
        foreach ($delete as $relationship) {
            if (!$relationship->delete()) {
                $success = false;
            }
        }

        foreach ($save as $relationship) {
            if (!$relationship->save()) {
                $success = false;
            }
        }

        $this->mutated = false;

        if (!$success && $this->element) {
            $this->element->addError($this->field->handle, 'Unable to save relationship.');
        }

        return $success;
    }


    /************************************************************
     * UTILITIES
     ************************************************************/

    /**
     * @inheritDoc
     */
    public function clear(): RelationshipInterface
    {
        $this->newRelations([]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function reset(): RelationshipInterface
    {
        $this->relations = null;
        $this->mutated = false;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isMutated(): bool
    {
        return $this->mutated;
    }

    /**
     * @inheritDoc
     */
    public function exists($object): bool
    {
        return null !== $this->findKey($object);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return $this->getCollection()->count();
    }


    /************************************************************
     * DELTA
     ************************************************************/

    /**
     * @inheritDoc
     */
    protected function delta(): array
    {
        $existingAssociations = $this->associationQuery()
            ->indexBy('targetId')
            ->all();

        $associations = [];
        $order = 1;

        /** @var Association $newAssociation */
        foreach ($this->getRelationships() as $newAssociation) {
            $association = ArrayHelper::remove(
                $existingAssociations,
                $newAssociation->getTargetId()
            );

            $newAssociation->sortOrder = $order++;

            /** @var Association $association */
            $association = $association ?: $newAssociation;

            // Has anything changed?
            if (!$association->getIsNewRecord() && !$this->hasChanged($newAssociation, $association)) {
                continue;
            }

            $associations[] = $this->sync($association, $newAssociation);
        }

        return [$associations, $existingAssociations];
    }

    /**
     * @param Association $new
     * @param Association $existing
     * @return bool
     */
    private function hasChanged(Association $new, Association $existing): bool
    {
        return $this->field->ensureSortOrder() &&
            $new->sortOrder != $existing->sortOrder;
    }

    /**
     * @param Association $from
     * @param Association $to
     *
     * @return Association
     */
    private function sync(Association $to, Association $from): Association
    {
        $to->sortOrder = $from->sortOrder;

        $to->ignoreSortOrder();

        return $to;
    }


    /*******************************************
     * RESOLVERS
     *******************************************/

    /**
     * Ensure we're working with an array of objects, not configs, etc
     *
     * @param array|QueryInterface|Collection|ElementInterface|Association $objects
     * @return array
     */
    protected function objectArray($objects): array
    {
        if ($objects instanceof QueryInterface || $objects instanceof Collection) {
            $objects = $objects->all();
        }

        // proper array
        if (!is_array($objects) || ArrayHelper::isAssociative($objects)) {
            $objects = [$objects];
        }

        return array_filter($objects);
    }


    /*******************************************
     * COLLECTION UTILS
     *******************************************/

    /**
     * @param Association[] $associations
     * @param bool $mutated
     * @return static
     */
    protected function newRelations(array $associations, bool $mutated = true): self
    {
        $this->relations = $this->createRelations($associations);
        $this->mutated = $mutated;

        return $this;
    }

    /**
     * @param Association $association
     * @return static
     */
    protected function addToRelations(Association $association): self
    {
        if (null === $this->relations) {
            return $this->newRelations([$association], true);
        }

        $this->insertCollection($this->relations, $association);
        $this->mutated = true;

        return $this;
    }

    /**
     * @param int $key
     * @return static
     */
    protected function removeFromRelations(int $key): self
    {
        $this->relations->forget($key);
        $this->mutated = true;

        return $this;
    }

    /**
     * @param array $associations
     * @return Collection
     */
    protected function createRelations(array $associations = []): Collection
    {
        $collection = new Collection();
        foreach ($associations as $association) {
            $this->insertCollection($collection, $association);
        }

        return $collection;
    }

    /**
     * Position the relationship based on the sort order
     *
     * @inheritDoc
     */
    protected function insertCollection(Collection $collection, Association $association)
    {
        if ($this->field->ensureSortOrder() && $association->sortOrder > 0) {
            $collection->splice($association->sortOrder - 1, 0, [$association]);
            return;
        }

        $collection->push($association);
    }

    /**
     * @inheritDoc
     */
    protected function updateCollection(Collection $collection, Association $association)
    {
        if (!$this->field->ensureSortOrder()) {
            return;
        }

        if (null !== ($key = $this->findKey($association))) {
            $collection->offsetUnset($key);
        }

        $this->insertCollection($collection, $association);
    }


    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @return AssociationQuery
     */
    protected function associationQuery(): AssociationQuery
    {
        return Association::find()
            ->setSource($this->element->getId() ?: false)
            ->setField($this->field)
            ->orderBy([
                'sortOrder' => SORT_ASC
            ])
            ->limit(null);
    }


    /*******************************************
     * CREATE
     *******************************************/

    /**
     * Create a new relationship object
     *
     * @param $object
     * @return Association
     */
    protected function create($object): Association
    {
        if ($object instanceof Association) {
            return $object;
        }

        $element = $this->resolveElement($object);

        return new Association([
            'fieldId' => $this->field->id,
            'sourceId' => $this->element ? $this->element->getId() : null,
            'targetId' => $element->getId()
        ]);
    }


    /*******************************************
     * UTILS
     *******************************************/

    /**
     * @param UserAssociation|int|array|null $object
     * @return int|null
     */
    protected function findKey($object = null)
    {
        if ($object instanceof Association) {
            return $this->findRelationshipKey($object->targetId);
        }

        if (null === ($element = $this->resolveElement($object))) {
            return null;
        }

        return $this->findRelationshipKey($element->getId());
    }

    /**
     * @param $identifier
     * @return int|string|null
     */
    private function findRelationshipKey($identifier)
    {
        /** @var Association $association */
        foreach ($this->getRelationships()->all() as $key => $association) {
            if ($association->targetId == $identifier) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param ElementInterface|Association|int|array|null $element
     * @return ElementInterface|null
     */
    protected function resolveElement($element = null)
    {
        if (null === $element) {
            return null;
        }

        if ($element instanceof ElementInterface) {
            return $element;
        }

        if ($element instanceof Association) {
            $element = $element->targetId;
        }

        if (is_array($element) &&
            null !== ($id = ArrayHelper::getValue($element, 'id'))
        ) {
            $element = $id;
        }

        return $this->field->resolveElement($element);
    }


    /*******************************************
     * MAGIC (pass calls onto query)
     *******************************************/

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (UnknownPropertyException $e) {
            return $this->field->getQuery($this->element)->{$name};
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        try {
            return parent::__set($name, $value);
        } catch (UnknownPropertyException $e) {
            return $this->field->getQuery($this->element)->{$name}($value);
        }
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public function __call($name, $params)
    {
        /** @var ElementQuery $query */
        $query = $this->field->getQuery($this->element);
        if ($query->hasMethod($name)) {
            return call_user_func_array([$query, $name], $params);
        }

        return parent::__call($name, $params);
    }
}
