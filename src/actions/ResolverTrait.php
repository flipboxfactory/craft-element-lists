<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\craft\element\lists\actions;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use flipbox\craft\element\lists\fields\ElementListInterface;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
trait ResolverTrait
{
    /**
     * @param string $field
     * @return ElementListInterface
     * @throws HttpException
     */
    protected function resolveField(string $field): ElementListInterface
    {
        $field = is_numeric($field) ?
            Craft::$app->getFields()->getFieldbyId($field) :
            Craft::$app->getFields()->getFieldByHandle($field);

        if (!$field instanceof ElementListInterface) {
            throw new HttpException(400, sprintf(
                "Field must be an instance of '%s', '%s' given.",
                ElementListInterface::class,
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
}
