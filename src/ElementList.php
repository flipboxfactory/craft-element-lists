<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\services\Fields;
use craft\web\View;
use flipbox\craft\element\lists\fields\AssetList;
use flipbox\craft\element\lists\fields\CategoryList;
use flipbox\craft\ember\modules\LoggerTrait;
use flipbox\craft\element\lists\fields\UserList;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ElementList extends Plugin
{
    use LoggerTrait;

    /**
     * This module's logger category
     *
     * @var string
     */
    protected static $category = 'element-lists';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = UserList::class;
                $event->types[] = CategoryList::class;
                $event->types[] = AssetList::class;
            }
        );

        // Base template directory
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $e) {
                $e->roots['nested-element-index'] = Craft::$app->getPath()->getVendorPath() .
                    '/flipboxfactory/craft-elements-nested-index/src/templates';
            }
        );
    }


    /*******************************************
     * TRANSLATE
     *******************************************/

    /**
     * Translates a message to the specified language.
     *
     * This is a shortcut method of [[\Craft::t()]].
     *     *
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function t($message, $params = [], $language = null)
    {
        return Craft::t(static::$category, $message, $params, $language);
    }
}
