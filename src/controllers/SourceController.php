<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\element\lists\actions\source\Associate;
use flipbox\element\lists\actions\source\Dissociate;
use flipbox\ember\controllers\AbstractController;
use flipbox\ember\filters\FlashMessageFilter;
use flipbox\ember\filters\ModelErrorFilter;
use flipbox\ember\filters\RedirectFilter;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SourceController extends AbstractController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'redirect' => [
                    'class' => RedirectFilter::class,
                    'only' => ['associate', 'dissociate'],
                    'actions' => [
                        'associate' => [204],
                        'dissociate' => [204],
                    ]
                ],
                'error' => [
                    'class' => ModelErrorFilter::class
                ],
                'flash' => [
                    'class' => FlashMessageFilter::class,
                    'actions' => [
                        'associate' => [
                            204 => Craft::t('element-lists', "Element successfully associated."),
                            401 => Craft::t('element-lists', "Failed to associate element.")
                        ],
                        'dissociate' => [
                            204 => Craft::t('element-lists', "Element successfully dissociated."),
                            401 => Craft::t('element-lists', "Failed to dissociate element.")
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * @return array
     */
    protected function verbs(): array
    {
        return [
            'associate' => ['post'],
            'dissociate' => ['post', 'delete'],
        ];
    }


    /**
     * @param string|int|null $source
     * @param string|int|null $target
     * @param string|int|null $field
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAssociate($source = null, $target = null, $field = null)
    {
        if (null === $source) {
            $source = Craft::$app->getRequest()->getBodyParam('source');
        }

        if (null === $target) {
            $target = Craft::$app->getRequest()->getBodyParam('target');
        }

        if (null === $field) {
            $field = Craft::$app->getRequest()->getBodyParam('field');
        }

        /** @var Associate $action */
        $action = Craft::createObject([
            'class' => Associate::class,
            'checkAccess' => [$this, 'checkAssociateAccess']
        ], [
            'associate',
            $this
        ]);

        return $action->runWithParams([
            'source' => $source,
            'target' => $target,
            'field' => $field
        ]);
    }

    /**
     * @param string|int|null $source
     * @param string|int|null $target
     * @param string|int|null $field
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDissociate($source = null, $target = null, $field = null)
    {
        if (null === $source) {
            $source = Craft::$app->getRequest()->getBodyParam('source');
        }

        if (null === $target) {
            $target = Craft::$app->getRequest()->getBodyParam('target');
        }

        if (null === $field) {
            $field = Craft::$app->getRequest()->getBodyParam('field');
        }

        /** @var Dissociate $action */
        $action = Craft::createObject([
            'class' => Dissociate::class,
            'checkAccess' => [$this, 'checkDissociateAccess']
        ], [
            'dissociate',
            $this
        ]);

        return $action->runWithParams([
            'source' => $source,
            'target' => $target,
            'field' => $field
        ]);
    }

    /**
     * @return bool
     */
    public function checkAssociateAccess(): bool
    {
        return $this->checkAdminAccess();
    }

    /**
     * @return bool
     */
    public function checkDissociateAccess(): bool
    {
        return $this->checkAdminAccess();
    }

    /**
     * @return bool
     */
    protected function checkAdminAccess()
    {
        $this->requireLogin();
        return Craft::$app->getUser()->getIsAdmin();
    }
}
