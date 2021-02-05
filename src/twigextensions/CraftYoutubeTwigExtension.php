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
use LitEmoji\LitEmoji;
use Craft;

/**
 * @author    Thomas Sømoen
 * @package   CraftYoutube
 * @since     1.0.0
 */
class CraftYoutubeTwigExtension extends \Twig\Extension\AbstractExtension
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
            new \Twig\TwigFilter('youtube_duration', [$this, 'formatDuration']),
            new \Twig\TwigFilter('emoji_shortcode', [$this, 'emojiShortcode']),
            new \Twig\TwigFilter('emoji_html', [$this, 'emojiHTML']),
            new \Twig\TwigFilter('emoji_unicode', [$this, 'emojiUnicode']),
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

    /**
     * @param null $text
     *
     * @return string
     */
    public function emojiShortcode($text)
    {
        return LitEmoji::encodeShortcode($text);
    }

    /**
     * @param null $text
     *
     * @return string
     */
    public function emojiHTML($text)
    {
        return LitEmoji::encodeHtml($text);
    }

    /**
     * @param null $text
     *
     * @return string
     */
    public function emojiUnicode($text)
    {
        return LitEmoji::encodeUnicode($text);
    }
}
