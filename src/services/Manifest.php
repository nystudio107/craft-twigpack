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

namespace nystudio107\twigpack\services;

use nystudio107\twigpack\Twigpack;
use nystudio107\twigpack\helpers\Manifest as ManifestHelper;

use craft\base\Component;

use yii\web\NotFoundHttpException;

/** @noinspection MissingPropertyAnnotationsInspection */

/**
 * @author    nystudio107
 * @package   Twigpack
 * @since     1.0.0
 */
class Manifest extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Return the HTML tags to include the CSS
     *
     * @param string     $moduleName
     * @param bool       $async
     * @param null|array $config
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function getCssModuleTags(string $moduleName, bool $async = false, $config = null): string
    {
        $settings = Twigpack::$plugin->getSettings();
        $config = $config ?? $settings->getAttributes();

        return ManifestHelper::getCssModuleTags($config, $moduleName, $async);
    }

    /**
     * Returns the CSS file in $path wrapped in <style></style> tags
     *
     * @param $path
     *
     * @return string
     */
    public function getCssInlineTags(string $path): string
    {
        return ManifestHelper::getCssInlineTags($path);
    }

    /**
     * @param array       $config
     * @param null|string $name
     *
     * @return string
     */
    public function getCriticalCssTags($name = null, $config = null): string
    {
        $settings = Twigpack::$plugin->getSettings();
        $config = $config ?? $settings->getAttributes();

        return ManifestHelper::getCriticalCssTags($config, $name);
    }

    /**
     * Returns the uglified loadCSS rel=preload Polyfill as per:
     * https://github.com/filamentgroup/loadCSS#how-to-use-loadcss-recommended-example
     *
     * @return string
     */
    public function getCssRelPreloadPolyfill(): string
    {
        return ManifestHelper::getCssRelPreloadPolyfill();
    }

    /**
     * Return the HTML tags to include the JavaScript module
     *
     * @param string     $moduleName
     * @param bool       $async
     * @param null|array $config
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    public function getJsModuleTags(string $moduleName, bool $async = false, $config = null)
    {
        $settings = Twigpack::$plugin->getSettings();
        $config = $config ?? $settings->getAttributes();

        return ManifestHelper::getJsModuleTags($config, $moduleName, $async);
    }

    /**
     * Return the Safari 10.1 nomodule JavaScript fix
     *
     * @return string
     */
    public function getSafariNomoduleFix(): string
    {
        return ManifestHelper::getSafariNomoduleFix();
    }

    /**
     * Return the URI to a module
     *
     * @param string $moduleName
     * @param string $type
     * @param null   $config
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    public function getModule(string $moduleName, string $type = 'modern', $config = null)
    {
        $settings = Twigpack::$plugin->getSettings();
        $config = $config ?? $settings->getAttributes();

        return ManifestHelper::getModule($config, $moduleName, $type);
    }

    /**
     * Return the HASH value from a module
     *
     * @param string $moduleName
     * @param string $type
     * @param null   $config
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    public function getModuleHash(string $moduleName, string $type = 'modern', $config = null)
    {
        $settings = Twigpack::$plugin->getSettings();
        $config = $config ?? $settings->getAttributes();

        return ManifestHelper::getModuleHash($config, $moduleName, $type);
    }

    /**
     * Returns the contents of a file from a URI path
     *
     * @param string $path
     *
     * @return string
     */
    public function getFile(string $path): string
    {
        return ManifestHelper::getFile($path);
    }

    /**
     * Returns the contents of a file from the $fileName in the manifest
     *
     * @param string $fileName
     * @param string $type
     * @param null   $config
     *
     * @return string
     */
    public function getFileFromManifest(string $fileName, string $type = 'legacy', $config = null): string
    {
        $settings = Twigpack::$plugin->getSettings();
        $config = $config ?? $settings->getAttributes();

        return ManifestHelper::getFileFromManifest($config, $fileName, $type);
    }

    /**
     * Invalidate the manifest cache
     */
    public function invalidateCaches()
    {
        ManifestHelper::invalidateCaches();
    }
}
