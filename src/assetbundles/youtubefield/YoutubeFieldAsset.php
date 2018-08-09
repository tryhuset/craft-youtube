<?php
/**
 * Craft Youtube plugin for Craft CMS 3.x
 *
 * Add youtube field
 *
 * @link      https://apt.no/
 * @copyright Copyright (c) 2018 Thomas Sømoen
 */

namespace apt\craftyoutube\assetbundles\youtubefield;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Thomas Sømoen
 * @package   CraftYoutube
 * @since     1.0.0
 */
class YoutubeFieldAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@apt/craftyoutube/assetbundles/youtubefield/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Youtube.js',
        ];

        $this->css = [
            'css/Youtube.css',
        ];

        parent::init();
    }
}
