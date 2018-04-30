<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\controllers;

use Craft;
use craft\base\Element;
use craft\events\RegisterElementActionsEvent;
use flipbox\element\lists\elements\actions\RemoveSourceElements;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ElementIndexesController extends \craft\controllers\ElementIndexesController
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        Event::on(
            Element::class,
            Element::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $event->actions = [
                    [
                        'type' => RemoveSourceElements::class,
                        'sourceId' => $event->data['sourceId'] ?? null,
                        'fieldId' => $event->data['fieldId'] ?? null
                    ]
                ];
            },
            [
                'sourceId' => $this->sourceId(),
                'fieldId' => $this->fieldId()
            ]
        );
        parent::init();
    }

    /**
     * @return mixed
     */
    private function sourceId()
    {
        return Craft::$app->getRequest()->getParam('sourceId');
    }

    /**
     * @return mixed
     */
    private function fieldId()
    {
        return Craft::$app->getRequest()->getParam('fieldId');
    }
}
