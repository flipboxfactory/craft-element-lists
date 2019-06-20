<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\records;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use flipbox\craft\ember\records\ActiveRecordTrait;
use yii\base\Model;

/**
 * This trait assists w/ managing a 'targetId' attribute and a 'target' object, keeping them
 * in sync if one of the other is altered.  In addition, depending on the operation process, a
 * newly created object could be set and saved.  Subsequent calls to retrieve the Id would return
 * the newly created object Id.
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
trait TargetAttributeTrait
{
    use ActiveRecordTrait;

    /**
     * @var Element|ElementInterface|null
     */
    private $target;

    /**
     * @param string $attribute
     * @return Element|ElementInterface|null
     */
    abstract protected function resolveElementFromIdAttribute(string $attribute);

    /**
     * @param mixed $element
     * @return Element|ElementInterface|null
     */
    abstract protected function resolveElement($element = null);

    /**
     * @return bool
     */
    public function isTargetSet(): bool
    {
        return null !== $this->target;
    }

    /**
     * @return array
     */
    protected function targetRules(): array
    {
        return [
            [
                [
                    'targetId'
                ],
                'number',
                'integerOnly' => true
            ],
            [
                [
                    'targetId',
                    'target'
                ],
                'safe',
                'on' => [
                    Model::SCENARIO_DEFAULT
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function targetAttributeLabels(): array
    {
        return [
            'targetId' => Craft::t('app', 'Target Id')
        ];
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setTargetId(int $id = null)
    {
        $this->setAttribute('targetId', $id);

        if (null !== $this->target && $id !== $this->target->getId()) {
            $this->target = null;
        }
        return $this;
    }

    /**
     * Get associated targetId
     *
     * @return int|null
     */
    public function getTargetId()
    {
        if (null === $this->getAttribute('targetId') && null !== $this->target) {
            $this->setTargetId($this->target->getId());
        }

        return $this->getAttribute('targetId');
    }

    /**
     * @param mixed $target
     * @return $this
     */
    public function setTarget($target = null)
    {
        $this->target = null;
        $this->setAttribute('targetId', null);

        if ($target = $this->resolveElement($target)) {
            $this->target = $target;
            $this->setAttribute('targetId', $target->getId());
        }

        return $this;
    }

    /**
     * @return Element|ElementInterface|null
     */
    public function getTarget()
    {
        if ($this->target === null) {
            $target = $this->resolveElementFromIdAttribute('targetId');
            $this->setTarget($target);
            return $target;
        }

        $targetId = $this->getAttribute('targetId');
        if ($targetId !== null && $targetId !== $this->target->getId()) {
            $this->target = null;
            return $this->getTarget();
        }

        return $this->target;
    }
}
