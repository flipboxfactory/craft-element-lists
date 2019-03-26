<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\migrations;

use craft\db\Migration;
use craft\helpers\Json;
use craft\records\Field;
use flipbox\craft\element\lists\fields\UserList;

class m190326_080222_namespace extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $records = Field::find()
            ->andWhere(
                [
                'type' => "flipbox\\element\\lists\\fields\\UserSourceList"
                ]
            )
            ->all();

        $success = true;

        /**
 * @var Field $record
*/
        foreach ($records as $record) {
            $record->type = UserList::class;

            $settings = $record->settings ?? [];
            if (is_string($settings)) {
                $settings = Json::decodeIfJson($settings);
            }

            // Update settings
            $record->settings = $settings;

            // Save
            if (!$record->save()) {
                $success = false;
            }
        }

        return $success;
    }
}
