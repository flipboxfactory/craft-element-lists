<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\records;

use Craft;
use craft\models\Site as SiteModel;
use craft\records\Site as SiteRecord;
use craft\validators\SiteIdValidator;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\craft\ember\records\ActiveRecordTrait;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property int|null $sourceSiteId
 *
 * @method SiteModel parentResolveSite()
 */
trait SourceSiteAttributeTrait
{
    use ActiveRecordTrait;

    /**
     * @var SiteModel|null
     */
    private $site;

    /**
     * @return array
     */
    protected function sourceSiteRules(): array
    {
        return [
            [
                [
                    'sourceSiteId'
                ],
                'number',
                'integerOnly' => true
            ],
            [
                [
                    'sourceSiteId'
                ],
                SiteIdValidator::class
            ],
            [
                [
                    'sourceSiteId',
                    'sourceSite',
                    'siteId',
                    'site'
                ],
                'safe',
                'on' => [
                    static::SCENARIO_DEFAULT
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function sourceSiteAttributes(): array
    {
        return [
            'sourceSiteId'
        ];
    }

    /**
     * @inheritdoc
     */
    public function sourceSiteAttributeLabels(): array
    {
        return [
            'sourceSiteId' => Craft::t('app', 'Site Id')
        ];
    }

    /**
     * @param int|null $id
     * @return SourceSiteAttributeTrait
     */
    public function setSiteId(int $id = null)
    {
        return $this->setSourceSiteId($id);
    }

    /**
     * @return int|null
     */
    public function getSiteId()
    {
        return $this->getSourceSiteId();
    }

    /**
     * Associate a site
     *
     * @param mixed $site
     * @return $this
     */
    public function setSite($site = null)
    {
        return $this->setSourceSite($site);
    }

    /**
     * @return SiteModel|null
     */
    public function getSite()
    {
        return $this->getSourceSite();
    }

    /**
     * Set associated sourceSiteId
     *
     * @param int|null $id
     * @return $this
     */
    public function setSourceSiteId(int $id = null)
    {
        $this->sourceSiteId = $id;
        return $this;
    }

    /**
     * Get associated sourceSiteId
     *
     * @return int|null
     */
    public function getSourceSiteId()
    {
        $siteId = $this->getAttribute('sourceSiteId');
        if (null === $siteId && null !== $this->site) {
            $siteId = $this->sourceSiteId = $this->site->id;
        }

        return $siteId;
    }

    /**
     * Associate a site
     *
     * @param mixed $site
     * @return $this
     */
    public function setSourceSite($site = null)
    {
        $this->site = null;

        if (($site = SiteHelper::resolve($site)) === null) {
            $this->site = $this->sourceSiteId = null;
        } else {
            $this->sourceSiteId = $site->id;
            $this->site = $site;
        }

        return $this;
    }

    /**
     * @return SiteModel|null
     */
    public function getSourceSite()
    {
        if ($this->site === null) {
            $site = $this->resolveSite();
            $this->setSourceSite($site);
            return $site;
        }

        $sourceSiteId = $this->sourceSiteId;
        if ($sourceSiteId !== null &&
            $sourceSiteId !== $this->site->id
        ) {
            $this->site = null;
            return $this->getSite();
        }

        return $this->site;
    }

    /**
     * @return SiteModel|null
     * @throws \yii\base\InvalidArgumentException
     */
    protected function resolveSite()
    {
        if ($site = $this->resolveSiteFromRelation()) {
            return $site;
        }

        if ($site = $this->resolveSiteFromId()) {
            return $site;
        }

        return null;
    }

    /**
     * @return SiteModel|null
     * @throws \yii\base\InvalidArgumentException
     */
    private function resolveSiteFromRelation()
    {
        if (false === $this->isRelationPopulated('siteRecord')) {
            return null;
        }

        if (null === ($record = $this->getRelation('siteRecord'))) {
            return null;
        }

        /** @var SiteRecord $record */

        return Craft::$app->getSites()->getSiteById($record->id);
    }

    /**
     * @return SiteModel|null
     */
    private function resolveSiteFromId()
    {
        if (null === $this->sourceSiteId) {
            return null;
        }

        return Craft::$app->getSites()->getSiteById($this->sourceSiteId);
    }

    /**
     * Get the associated Site
     *
     * @return ActiveQueryInterface
     */
    public function getSiteRecord()
    {
        return $this->hasOne(
            SiteRecord::class,
            ['sourceSiteId' => 'id']
        );
    }
}
