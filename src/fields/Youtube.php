<?php

/**
 * Craft Youtube plugin for Craft CMS 3.x
 *
 * Add youtube field
 *
 * @link      https://apt.no/
 * @copyright Copyright (c) 2018 Thomas Sømoen
 */

namespace apt\craftyoutube\fields;

use apt\craftyoutube\CraftYoutube;
use apt\craftyoutube\assetbundles\youtubefield\YoutubeFieldAsset;
use apt\craftyoutube\models\Film;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;

/**
 * @author    Thomas Sømoen
 * @package   CraftYoutube
 * @since     1.0.0
 */
class Youtube extends Field
{
    // Private Properties
    // =========================================================================


    // Public Properties
    // =========================================================================

    /**
     * @var boolean
     */
    public $title = true;

    /**
     * @var boolean
     */
    public $description = true;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('craft-youtube', 'Youtube');
    }

    // Public Methods
    // =========================================================================

    public function __construct($config = [])
    {
        if (isset($config['title'])) {
            $config['title'] = (bool) $config['title'];
        }
        if (isset($config['description'])) {
            $config['description'] = (bool) $config['description'];
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof Film) {
            $value->setRequired([
                'title' => $this->title,
                'description' => $this->description,
            ]);

            return $value;
        }

        $model = new Film();
        $model->setRequired([
            'title' => $this->title,
            'description' => $this->description,
        ]);

        if (empty($value)) {
            return $model;
        }

        if (is_array($value)) {
            if (empty($value['url'])) {
                return $model;
            }

            // If url hasn't changed, only update text fields.
            if (isset($value['prev']) && $value['url'] === $value['prev']) {
                $model = CraftYoutube::getInstance()->youtube->get($value, [
                    'title' => $this->title,
                    'description' => $this->description,
                ]);

            // IF url has changed, look up new video
            } else {
                $model = CraftYoutube::getInstance()->youtube->get([
                    'prev' => $value['prev'],
                    'url' => $value['url'],
                ], [
                    'title' => $this->title,
                    'description' => $this->description,
                ]);
            }
        } else {
            $json = json_decode($value, true);
            unset($json['__model__']);
            $model = new Film($json);
        }
        $model->setRequired([
            'title' => $this->title,
            'description' => $this->description,
        ]);
        return $model;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['title', 'boolean'],
            ['description', 'boolean'],
            [['title', 'description'], 'default', 'value' => false]
        ];
    }

    public function getElementValidationRules(): array
    {
        return ['validateYoutubeData'];
    }

    /**
     * Validates the YOutube data.
     *
     * @param ElementInterface $element
     */
    public function validateYoutubeData(ElementInterface $element)
    {
        /** @var Element $element */
        $model = $element->getFieldValue($this->handle);
        $errors = $model->getErrors();
        $model->validate();
        foreach ($errors as $attribute => $error) {
            foreach ($error as $message) {
                $model->addError($attribute, Craft::t('craft-youtube', $message));
            }
        }

        if ($model->hasErrors()) {
            $element->addModelErrors($model, 'youtube');
        }
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'craft-youtube/_components/fields/Youtube_settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'craft-youtube/_components/fields/Youtube_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'required' => [
                    'title' => $this->title,
                    'description' => $this->description,
                ],
            ]
        );
    }
}
