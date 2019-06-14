<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\relationships;

use Craft;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\craft\element\lists\ElementList;
use flipbox\craft\element\lists\fields\RelationalInterface;
use flipbox\craft\element\lists\queries\AssociationQuery;
use flipbox\craft\element\lists\records\Association;
use flipbox\craft\ember\helpers\QueryHelper;
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
     * @var RelationalInterface
     */
    private $field;

    /**
     * The association records
     *
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
                $this->field->getQuery($this->element)->all()
            );
        };

        return Collection::make(
            $this->field->getQuery($this->element)
                ->id(
                    $this->relations
                        ->sortBy('sortOrder')
                        ->pluck('targetId')
                        ->all()
                )
                ->fixedOrder(true)
                ->limit(null)
                ->all()
        );
    }

    /**
     * @inheritDoc
     */
    public function getRelationships(): Collection
    {
        if (null === $this->relations) {
            $this->newRelations($this->query()->all(), false);
        }

        return $this->relations;
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
            if (null === ($association = $this->findOne($object))) {
                $association = $this->create($object);
                $this->addToRelations($association);
            }

            if (!empty($attributes)) {
                Craft::configure(
                    $association,
                    $attributes
                );

                $this->mutated = true;
            }
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
     * COMMIT
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

        list($newAssociations, $existingAssociations) = $this->delta();

        // Delete those removed
        foreach ($existingAssociations as $existingAssociation) {
            if (!$existingAssociation->delete()) {
                $success = false;
            }
        }

        foreach ($newAssociations as $newAssociation) {
            if (!$newAssociation->save()) {
                $success = false;
            }
        }

        $this->newRelations($newAssociations);
        $this->mutated = false;

        if (!$success && $this->element) {
            $this->element->addError($this->field->handle, 'Unable to save relationship.');
        }

        return $success;
    }

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

    /**
     * @inheritDoc
     */
    protected function delta(): array
    {
        $existingAssociations = $this->query()
            ->indexBy('targetId')
            ->all();

        $associations = [];
        $order = 1;
        /** @var Association $newAssociation */
        foreach ($this->getRelationships()->sortBy('sortOrder') as $newAssociation) {
            if (null === ($association = ArrayHelper::remove(
                $existingAssociations,
                $newAssociation->targetId
            ))
            ) {
                $association = $newAssociation;
            }

            $association->sourceId = $newAssociation->sourceId;
            $association->targetId = $newAssociation->targetId;
            $association->fieldId = $newAssociation->fieldId;
            $association->sourceSiteId = $newAssociation->sourceSiteId;
            $association->sortOrder = $order++;

            $associations[] = $association;
        }

        return [$associations, $existingAssociations];
    }


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
     * CACHE
     *******************************************/

    /**
     * @param Association[] $associations
     * @param bool $mutated
     * @return static
     */
    protected function newRelations(array $associations, bool $mutated = true): self
    {
        $this->relations = Collection::make($associations);
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

        $this->relations->push($association);
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
     * @param array $criteria
     * @return AssociationQuery
     */
    protected function query(array $criteria = []): AssociationQuery
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $query = Association::find()
            ->setSource($this->element->getId() ?: false)
            ->orderBy([
                'sortOrder' => SORT_ASC
            ]);

        if (!empty($criteria)) {
            QueryHelper::configure(
                $query,
                $criteria
            );
        }

        return $query;
    }

    /**
     * Create a new relationship object
     *
     * @param $object
     * @return Association
     */
    protected function create($object): Association
    {
        $element = $this->resolveElement($object);

        return new Association([
            'field' => $this->field->id,
            'sourceId' => $this->element ? $this->element->getId() : null,
            'targetId' => $element->getId()
        ]);
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

    /**
     * @param UserAssociation|int|array|null $object
     * @return int|null
     */
    protected function findKey($object = null)
    {
        if (null === ($element = $this->resolveElement($object))) {
            ElementList::info(sprintf(
                "Unable to resolve relationship: %s",
                (string)Json::encode($object)
            ));
            return null;
        }

        // Todo - perform this lookup via Collection method
        foreach ($this->getRelationships() as $key => $association) {
            if ($association->targetId == $element->getId()) {
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
