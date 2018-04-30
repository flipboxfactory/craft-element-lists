<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://www.flipboxfactory.com/software/element-lists/license
 * @link       https://www.flipboxfactory.com/software/element-lists/
 */

namespace flipbox\element\lists\records;

use flipbox\craft\sortable\associations\records\SortableAssociation;
use flipbox\craft\sortable\associations\services\SortableAssociations;
use flipbox\element\lists\db\SourceAssociationsQuery;
use flipbox\element\lists\ElementList;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\traits\SiteRules;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $fieldId
 * @property string $domain
 * @property int $elementId
 */
class Association extends SortableAssociation
{
    use SiteRules;

    /**
     * @inheritdoc
     */
    const TABLE_ALIAS = 'elementlist';

    /**
     * @inheritdoc
     */
    const TARGET_ATTRIBUTE = 'targetId';

    /**
     * @inheritdoc
     */
    const SOURCE_ATTRIBUTE = 'sourceId';

    /**
     * {@inheritdoc}
     * @return SourceAssociationsQuery
     */
    public static function find()
    {
        return ElementList::getInstance()->getSourceAssociations()->getQuery();
    }

    /*******************************************
     * ASSOCIATE / DISSOCIATE
     *******************************************/

    /**
     * @return SortableAssociations
     */
    protected function associationService(): SortableAssociations
    {
        return ElementList::getInstance()->getSourceAssociations();
    }

    /*******************************************
     * RULES
     *******************************************/

    /**
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            $this->siteRules(),
            [
                [
                    [
                        'fieldId',
                    ],
                    'required'
                ],
                [
                    [
                        'fieldId'
                    ],
                    'number',
                    'integerOnly' => true
                ],
                [
                    [
                        'fieldId',
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }
}
