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
use craft\events\RegisterElementSortOptionsEvent;
use flipbox\craft\element\lists\ElementList;
use flipbox\craft\element\lists\elements\actions\DissociateFromElementAction;
use flipbox\craft\element\lists\records\Association;
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
                    'orderBy' => Association::TABLE_ALIAS . '.sortOrder'
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
