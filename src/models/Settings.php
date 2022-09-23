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
use craft\helpers\App;

/**
 * @author    Thomas Sømoen
 * @package   CraftYoutube
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var bool
     */
    public $useApi = true;

    /**
     * @var string
     */
    public $googleApiKey = '';

    // Public Methods
    // =========================================================================

    public function getUseApi(): bool
    {
        $value = App::parseEnv($this->useApi);
        return boolval($value);
    }

    public function getApiKey()
    {
        return App::parseEnv($this->googleApiKey);
    }

    /**
     * @inheritdoc
     */
    public function rules() : array
    {
        if ($this->useApi) {
            return [
                ['useApi', 'boolean'],
                ['googleApiKey', 'string'],
                ['googleApiKey', 'required'],
                ['googleApiKey', 'default', 'value' => ''],
                ['useApi', 'default', 'value' => false],
            ];
        }
        return [
            ['googleApiKey', 'string'],
            ['googleApiKey', 'default', 'value' => ''],
        ];
    }
}
