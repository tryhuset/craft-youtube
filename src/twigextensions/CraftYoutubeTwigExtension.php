<?php
/**
 * Craft Youtube plugin for Craft CMS 3.x
 *
 * Add youtube field
 *
 * @link      https://apt.no/
 * @copyright Copyright (c) 2018 Thomas Sømoen
 */

namespace apt\craftyoutube\twigextensions;

use apt\craftyoutube\CraftYoutube;

use Craft;

/**
 * @author    Thomas Sømoen
 * @package   CraftYoutube
 * @since     1.0.0
 */
class CraftYoutubeTwigExtension extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'CraftYoutube';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('youtube_duration', [$this, 'formatDuration']),
        ];
    }

    /**
     * @param null $text
     *
     * @return string
     */
    public function formatDuration($duration)
    {
        return CraftYoutube::getInstance()->youtube->formatDuration($duration);
    }
}
