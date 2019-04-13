<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-element-lists/LICENSE
 * @link       https://github.com/flipboxfactory/craft-element-lists/
 */

namespace flipbox\craft\element\lists\queries;

use craft\models\Site;
use flipbox\craft\ember\queries\SiteAttributeTrait;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
trait SourceSiteAttributeTrait
{
    use SiteAttributeTrait;

    /**
     * @param string|string[]|int|int[]|Site|Site[]|null $value
     * @return static The query object
     */
    public function setSourceSite($value)
    {
        return $this->setSite($value);
    }

    /**
     * @param string|string[]|int|int[]|Site|Site[]|null $value
     * @return static The query object
     */
    public function sourceSite($value)
    {
        return $this->setSite($value);
    }

    /**
     * @param string|string[]|int|int[]|Site|Site[]|null $value
     * @return static The query object
     */
    public function setSourceSiteId($value)
    {
        return $this->setSite($value);
    }

    /**
     * @param string|string[]|int|int[]|Site|Site[]|null $value
     * @return static The query object
     */
    public function sourceSiteId($value)
    {
        return $this->setSite($value);
    }
}
