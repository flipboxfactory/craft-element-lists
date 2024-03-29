<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\controllers;

use Craft;
use craft\base\Element;
use craft\events\RegisterElementActionsEvent;
use flipbox\craft\element\lists\elements\actions\DissociateFromElementAction;
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
        // Disable db cache (query cache doesn't like relations)
        Craft::$app->getDb()->enableQueryCache = false;

        Event::on(
            Element::class,
            Element::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $event->actions = [
                    [
                        'type' => DissociateFromElementAction::class,
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

        $this->normalizeDisabledElementIds();

        parent::init();
    }

    /**
     * When working with large sets of elements, this may be sent as a comma delimited string
     */
    protected function normalizeDisabledElementIds()
    {
        $disabledElementIds = Craft::$app->getRequest()->getBodyParam('disabledElementIds', []);

        if (is_string($disabledElementIds)) {
            $disabledElementIds = explode(",", $disabledElementIds);

            Craft::$app->getRequest()->setBodyParams(array_merge(
                Craft::$app->getRequest()->getBodyParams(),
                [
                    'disabledElementIds' => $disabledElementIds
                ]
            ));
        }
    }

    /**
     * @return mixed
     */
    protected function sourceId()
    {
        return Craft::$app->getRequest()->getParam('sourceId');
    }

    /**
     * @return mixed
     */
    protected function fieldId()
    {
        return Craft::$app->getRequest()->getParam('fieldId');
    }
}
