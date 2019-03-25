<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\craft\element\lists\controllers;

use Craft;
use craft\base\Element;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterElementSortOptionsEvent;
use flipbox\craft\element\lists\ElementList;
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

        Event::on(
            Element::class,
            Element::EVENT_REGISTER_SORT_OPTIONS,
            function (RegisterElementSortOptionsEvent $event) {
                $event->sortOptions[] = [
                    'label' => ElementList::t('Field Order'),
                    'attribute' => 'field',
                    'orderBy' => 'elementlist.sortOrder'
                ];
            }
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
