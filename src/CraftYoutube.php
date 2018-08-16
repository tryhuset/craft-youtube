<?php
/**
 * Craft Youtube plugin for Craft CMS 3.x
 *
 * Add youtube field
 *
 * @link      https://apt.no/
 * @copyright Copyright (c) 2018 Thomas Sømoen
 */

namespace apt\craftyoutube;

use apt\craftyoutube\services\Youtube as YoutubeService;
use apt\craftyoutube\variables\CraftYoutubeVariable;
use apt\craftyoutube\twigextensions\CraftYoutubeTwigExtension;
use apt\craftyoutube\models\Settings;
use apt\craftyoutube\fields\Youtube as YoutubeField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;

use yii\base\Event;

/**
 * Class CraftYoutube
 *
 * @author    Thomas Sømoen
 * @package   CraftYoutube
 * @since     1.0.0
 *
 * @property  YoutubeService $youtube
 */
class CraftYoutube extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var CraftYoutube
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.5';

    // Public Methods
    // =========================================================================

    // public $hasCpSettings = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new CraftYoutubeTwigExtension());

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = YoutubeField::class;
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('craftYoutube', CraftYoutubeVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'craft-youtube',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'craft-youtube/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
