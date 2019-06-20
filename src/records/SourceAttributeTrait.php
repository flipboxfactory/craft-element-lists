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
 * This trait assists w/ managing a 'sourceId' attribute and a 'source' object, keeping them
 * in sync if one of the other is altered.  In addition, depending on the operation process, a
 * newly created object could be set and saved.  Subsequent calls to retrieve the Id would return
 * the newly created object Id.
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
trait SourceAttributeTrait
{
    use ActiveRecordTrait;

    /**
     * @var Element|ElementInterface|null
     */
    private $source;

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
    public function isSourceSet(): bool
    {
        return null !== $this->source;
    }

    /**
     * @return array
     */
    protected function sourceRules(): array
    {
        return [
            [
                [
                    'sourceId'
                ],
                'number',
                'integerOnly' => true
            ],
            [
                [
                    'sourceId',
                    'source'
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
    public function sourceAttributeLabels(): array
    {
        return [
            'sourceId' => Craft::t('app', 'Source Id')
        ];
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setSourceId(int $id = null)
    {
        $this->setAttribute('sourceId', $id);

        if (null !== $this->source && $id !== $this->source->getId()) {
            $this->source = null;
        }
        return $this;
    }

    /**
     * Get associated sourceId
     *
     * @return int|null
     */
    public function getSourceId()
    {
        if (null === $this->getAttribute('sourceId') && null !== $this->source) {
            $this->setSourceId($this->source->getId());
        }

        return $this->getAttribute('sourceId');
    }

    /**
     * @param mixed $source
     * @return $this
     */
    public function setSource($source = null)
    {
        $this->source = null;
        $this->setAttribute('sourceId', null);

        if ($source = $this->resolveElement($source)) {
            $this->source = $source;
            $this->setAttribute('sourceId', $source->getId());
        }

        return $this;
    }

    /**
     * @return Element|ElementInterface|null
     */
    public function getSource()
    {
        if ($this->source === null) {
            $source = $this->resolveElementFromIdAttribute('sourceId');
            $this->setSource($source);
            return $source;
        }

        $sourceId = $this->getAttribute('sourceId');
        if ($sourceId !== null && $sourceId !== $this->source->getId()) {
            $this->source = null;
            return $this->getSource();
        }

        return $this->source;
    }
}
