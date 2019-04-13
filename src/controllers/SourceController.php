<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\craft\element\lists\transformers\RecordResponseTransformer;
use flipbox\craft\ember\controllers\AbstractController;
use flipbox\craft\ember\filters\CallableFilter;
use flipbox\craft\ember\filters\FlashMessageFilter;
use flipbox\craft\ember\filters\ModelErrorFilter;
use flipbox\craft\ember\filters\RedirectFilter;
use flipbox\craft\element\lists\actions\source\Associate;
use flipbox\craft\element\lists\actions\source\Dissociate;
use flipbox\craft\element\lists\ElementList;
use yii\web\Response;

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
                        'dissociate' => [201],
                    ]
                ],
                'transform' => [
                    'class' => CallableFilter::class,
                    'formats' => [
                        '*' => Response::FORMAT_JSON
                    ],
                    'actions' => [
                        'associate' => [
                            RecordResponseTransformer::class,
                            'transform'
                        ],
                        'dissociate' => [
                            RecordResponseTransformer::class,
                            'transform'
                        ],
                    ]
                ],
                'error' => [
                    'class' => ModelErrorFilter::class
                ],
                'flash' => [
                    'class' => FlashMessageFilter::class,
                    'actions' => [
                        'associate' => [
                            204 => ElementList::t("Element successfully associated."),
                            400 => ElementList::t("Failed to associate element.")
                        ],
                        'dissociate' => [
                            201 => ElementList::t("Element successfully dissociated."),
                            400 => ElementList::t("Failed to dissociate element.")
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
     * @param string|int|null $site
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAssociate($source = null, $target = null, $field = null, $site = null)
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

        if (null === $site) {
            $site = Craft::$app->getRequest()->getBodyParam('site');
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
            'field' => $field,
            'siteId' => $site
        ]);
    }

    /**
     * @param string|int|null $source
     * @param string|int|null $target
     * @param string|int|null $field
     * @param string|int|null $site
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDissociate($source = null, $target = null, $field = null, $site = null)
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

        if (null === $site) {
            $site = Craft::$app->getRequest()->getBodyParam('site');
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
            'field' => $field,
            'siteId' => $site
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
