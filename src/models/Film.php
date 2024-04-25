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
    private $required = [
        'title' => true,
        'description' => true,
    ];

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
    public $title = '';

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

    public function setRequired($fields)
    {
        $this->required = array_merge($this->required, $fields);
    }

    public function isEmpty()
    {
        return empty($this->url);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = [
            [['url', 'duration'], 'string'],
            ['code', 'string', 'min' => 11, 'max' => 11],
            ['thumbnails', 'validateArray'],
        ];
        if ($this->required['title']) {
            $rules = array_merge($rules, [
                ['title', 'string'],
                ['title', 'required', 'message' => Craft::t('craft-youtube', 'Title cannot be blank.')],
            ]);
        }
        if ($this->required['description']) {
            $rules = array_merge($rules, [
                ['description', 'string'],
                ['description', 'required', 'message' => Craft::t('craft-youtube', 'Description cannot be blank.')],
            ]);
        }
        return $rules;
    }
}
