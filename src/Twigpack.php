<?php
/**
 * Twigpack plugin for Craft CMS 3.x
 *
 * Twigpack is the conduit between Twig and webpack, with manifest.json & webpack-dev-server HMR support
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\twigpack;

use craft\events\DeleteTemplateCachesEvent;
use craft\events\PluginEvent;
use craft\events\RegisterCacheOptionsEvent;
use craft\services\Plugins;
use craft\services\TemplateCaches;
use craft\utilities\ClearCaches;
use nystudio107\twigpack\services\Manifest as ManifestService;
use nystudio107\twigpack\models\Settings;
use nystudio107\twigpack\variables\ManifestVariable;

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

use nystudio107\fastcgicachebust\FastcgiCacheBust;
use yii\base\Exception;

/**
 * Class Twigpack
 *
 * @author    nystudio107
 * @package   Twigpack
 * @since     1.0.0
 *
 * @property  ManifestService $manifest
 */
class Twigpack extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Twigpack
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;
        // Install our event listeners
        $this->installEventListeners();
        // Log that we've loaded
        Craft::info(
            Craft::t(
                'twigpack',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * Clear all the caches!
     */
    public function clearAllCaches()
    {
        // Clear all of Twigpack's caches
        self::$plugin->manifest->invalidateCaches();
        // If the FastCGI Cache Bust plugin is installed, clear its caches too
        $plugin = Craft::$app->getPlugins()->getPlugin('fastcgi-cache-bust');
        if ($plugin !== null) {
            FastcgiCacheBust::$plugin->cache->clearAll();
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * Install our event listeners.
     */
    protected function installEventListeners()
    {
        // Handler: CraftVariable::EVENT_INIT
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('twigpack', ManifestVariable::class);
            }
        );
        // Handler: TemplateCaches::EVENT_AFTER_DELETE_CACHES
        Event::on(
            TemplateCaches::class,
            TemplateCaches::EVENT_AFTER_DELETE_CACHES,
            function (DeleteTemplateCachesEvent $event) {
                // Invalidate the caches when template caches are deleted
                $this->clearAllCaches();
            }
        );
        // Handler: Plugins::EVENT_AFTER_INSTALL_PLUGIN
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // Invalidate our caches after we've been installed
                    $this->clearAllCaches();
                }
            }
        );
        // Handler: ClearCaches::EVENT_REGISTER_CACHE_OPTIONS
        Event::on(
            ClearCaches::class,
            ClearCaches::EVENT_REGISTER_CACHE_OPTIONS,
            function (RegisterCacheOptionsEvent $event) {
                Craft::debug(
                    'ClearCaches::EVENT_REGISTER_CACHE_OPTIONS',
                    __METHOD__
                );
                // Register our caches for the Clear Cache Utility
                $event->options = array_merge(
                    $event->options,
                    $this->customAdminCpCacheOptions()
                );
            }
        );
    }


    /**
     * Returns the custom AdminCP cache options.
     *
     * @return array
     */
    protected function customAdminCpCacheOptions(): array
    {
        return [
            // Frontend template caches
            [
                'key' => 'twigpack-manfiest-caches',
                'label' => Craft::t('twigpack', 'Twigpack Manifest Cache'),
                'action' => [$this, 'clearAllCaches'],
            ],
        ];
    }

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
        try {
            return Craft::$app->view->renderTemplate(
                'twigpack/settings',
                [
                    'settings' => $this->getSettings(),
                ]
            );
        } catch (\Twig_Error_Loader $e) {
            Craft::error($e->getMessage(), __METHOD__);
        } catch (Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        return '';
    }
}
