<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\actions;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use flipbox\craft\ember\helpers\SiteHelper;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
trait ResolverTrait
{
    /**
     * @param string $field
     * @return FieldInterface
     * @throws HttpException
     */
    protected function resolveField(string $field): FieldInterface
    {
        $field = is_numeric($field) ?
            Craft::$app->getFields()->getFieldbyId($field) :
            Craft::$app->getFields()->getFieldByHandle($field);

        if (!$field instanceof FieldInterface) {
            throw new HttpException(400, sprintf(
                "Field must be an instance of '%s', '%s' given.",
                FieldInterface::class,
                get_class($field)
            ));
        }

        return $field;
    }

    /**
     * @param string $element
     * @return ElementInterface|Element
     * @throws HttpException
     */
    protected function resolveElement(string $element): ElementInterface
    {
        if (null === ($element = Craft::$app->getElements()->getElementById($element))) {
            throw new HttpException(400, 'Invalid element');
        };

        return $element;
    }

    /**
     * @param int|null $siteId
     * @return int|null
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function resolveSiteId(int $siteId = null)
    {
        if (!Craft::$app->getIsMultiSite() || Craft::$app->getSites()->getCurrentSite()->primary) {
            return null;
        }

        return SiteHelper::ensureSiteId($siteId);
    }
}
