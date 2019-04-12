<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\transformers;

use flipbox\craft\element\lists\records\Association;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.1
 */
class RecordResponseTransformer
{
    /**
     * @param Association $record
     * @return array
     */
    public function __invoke(Association $record)
    {
        return self::transform($record);
    }

    /**
     * @param Association $record
     * @return array
     */
    public static function transform(Association $record): array
    {
        if($record->hasErrors()) {
            return [
                'errors' => $record->getFirstErrors()
            ];
        }

        return $record->toArray();
    }
}
