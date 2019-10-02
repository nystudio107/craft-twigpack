<?php
/**
 * Twigpack plugin for Craft CMS 3.x
 *
 * Twigpack is the conduit between Twig and webpack, with manifest.json &
 * webpack-dev-server HMR support
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\twigpack;

use nystudio107\twigpack\services\Manifest as ManifestService;
use nystudio107\twigpack\models\Settings;
use nystudio107\twigpack\variables\ManifestVariable;

use Craft;
use craft\base\Plugin;
use craft\events\DeleteTemplateCachesEvent;
use craft\events\PluginEvent;
use craft\events\RegisterCacheOptionsEvent;
use craft\events\TemplateEvent;
use craft\services\Plugins;
use craft\services\TemplateCaches;
use craft\utilities\ClearCaches;
use craft\web\twig\variables\CraftVariable;
use craft\web\Application;
use craft\web\View;

use yii\base\Event;
use yii\web\NotFoundHttpException;

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

    /**
     * @var string
     */
    public static $templateName;

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
    }

    /**
     * Inject the error entry point JavaScript for auto-reloading of Twig error
     * pages
     */
    public function injectErrorEntry()
    {
        if (Craft::$app->getResponse()->isServerError || Craft::$app->getResponse()->isClientError) {
            $settings = self::$plugin->getSettings();
            if (!empty($settings->errorEntry) && $settings->useDevServer) {
                try {
                    $tags = self::$plugin->manifest->getJsModuleTags($settings->errorEntry, false);
                    if ($tags !== null) {
                        echo $tags;
                    }
                } catch (NotFoundHttpException $e) {
                    // That's okay, Twigpack will have already logged the error
                }
            }
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * Install our event listeners.
     */
    protected function installEventListeners()
    {
        // Remember the name of the currently rendering template
        // Handler: View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
            function (TemplateEvent $event) {
                self::$templateName = $event->template;
            }
        );
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
        // delay attaching event handler to the view component after it is fully configured
        $app = Craft::$app;
        if ($app->getConfig()->getGeneral()->devMode) {
            $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
                $app->getView()->on(View::EVENT_END_BODY, [$this, 'injectErrorEntry']);
            });
        }
    }

    /**
     * Returns the custom Control Panel cache options.
     *
     * @return array
     */
    protected function customAdminCpCacheOptions(): array
    {
        return [
            // Manifest cache
            [
                'key' => 'twigpack-manifest-cache',
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
}
