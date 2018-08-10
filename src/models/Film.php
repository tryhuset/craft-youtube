<?php
/**
 * Craft Youtube plugin for Craft CMS 3.x
 *
 * Add youtube field
 *
 * @link      https://apt.no/
 * @copyright Copyright (c) 2018 Thomas Sømoen
 */

namespace apt\craftyoutube\models;

use apt\craftyoutube\CraftYoutube;

use Craft;
use craft\base\Model;

/**
 * @author    Thomas Sømoen
 * @package   CraftYoutube
 * @since     1.0.0
 */
class Film extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $url = '';

    /**
     * @var string
     */
    public $code = '';

    /**
     * @var array
     */
    public $thumbnails = [];

    /**
     * @var string
     */
    public $title = 'Youtube film';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $duration = '';

    // Public Methods
    // =========================================================================

    public function __toString()
    {
        return $this->title;
    }

    public function validateArray($attribute)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, Craft::t('craft-youtube', '{attribute} must be an array', ['attribute' => Craft::t('craft-youtube', $attribute)]));
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'url', 'title', 'duration'], 'string'],
            [['title', 'code', 'description'], 'required'],
            ['code', 'string', 'min' => 11, 'max' => 11],
            ['thumbnails', 'validateArray'],
        ];
    }
}
