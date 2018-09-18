<?php
/**
 * Twigpack plugin for Craft CMS 3.x
 *
 * Twigpack is the conduit between Twig and webpack, with manifest.json & webpack-dev-server HMR support
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\twigpack\helpers;

use nystudio107\twigpack\Twigpack;

use craft\helpers\Json as JsonHelper;

use Craft;
use yii\caching\TagDependency;

/**
 * @author    nystudio107
 * @package   Twigpack
 * @since     1.0.0
 */
class Manifest
{
    // Constants
    // =========================================================================

    const CACHE_KEY = 'twigpack';
    const CACHE_TAG = 'twigpack';

    const DEVMODE_CACHE_DURATION = 1;

    // Protected Static Properties
    // =========================================================================

    /**
     * @var array
     */
    protected static $manifests;

    // Public Static Methods
    // =========================================================================

    /**
     * @param array  $config
     * @param string $moduleName
     * @param bool   $async
     *
     * @return null|string
     */
    public static function getCssModuleTags(array $config, string $moduleName, bool $async)
    {
        $legacyModule = self::getModule($config, $moduleName, 'legacy');
        if ($legacyModule === null) {
            return null;
        }
        $lines = [];
        if ($async) {
            $lines[] = "<link rel=\"preload\" href=\"{$legacyModule}\" as=\"style\" onload=\"this.rel='stylesheet'\" />";
            $lines[] = "<noscript><link rel=\"stylesheet\" href=\"{$legacyModule}\"></noscript>";
        } else {
            $lines[] = "<link rel=\"stylesheet\" href=\"{$legacyModule}\" />";
        }

        return implode("\r\n", $lines);
    }

    /**
     * @param array  $config
     * @param string $moduleName
     * @param bool   $async
     *
     * @return null|string
     */
    public static function getJsModuleTags(array $config, string $moduleName, bool $async)
    {
        $legacyModule = self::getModule($config, $moduleName, 'legacy');
        if ($legacyModule === null) {
            return null;
        }
        if ($async) {
            $modernModule = self::getModule($config, $moduleName, 'modern');
            if ($modernModule === null) {
                return null;
            }
        }
        $lines = [];
        if ($async) {
            $lines[] = "<script type=\"module\" src=\"{$modernModule}\"></script>";
            $lines[] = "<script nomodule src=\"{$legacyModule}\"></script>";
        } else {
            $lines[] = "<script src=\"{$legacyModule}\"></script>";
        }

        return implode("\r\n", $lines);
    }

    /**
     * Safari 10.1 supports modules, but does not support the `nomodule`
     * attribute - it will load <script nomodule> anyway. This snippet solve
     * this problem, but only for script tags that load external code, e.g.:
     * <script nomodule src="nomodule.js"></script>
     *
     * Again: this will **not* # prevent inline script, e.g.:
     * <script nomodule>alert('no modules');</script>.
     *
     * This workaround is possible because Safari supports the non-standard
     * 'beforeload' event. This allows us to trap the module and nomodule load.
     *
     * Note also that `nomodule` is supported in later versions of Safari -
     * it's just 10.1 that omits this attribute.
     *
     * c.f.: https://gist.github.com/samthor/64b114e4a4f539915a95b91ffd340acc
     *
     * @return string
     */
    public static function getSafariNomoduleFix(): string
    {
        return <<<EOT
<script>
!function(){var e=document,t=e.createElement("script");if(!("noModule"in t)&&"onbeforeload"in t){var n=!1;e.addEventListener("beforeload",function(e){if(e.target===t)n=!0;else if(!e.target.hasAttribute("nomodule")||!n)return;e.preventDefault()},!0),t.type="module",t.src=".",e.head.appendChild(t),t.remove()}}();
</script>
EOT;
    }

    /**
     * @param array  $config
     * @param string $moduleName
     * @param string $type
     *
     * @return null|string
     */
    public static function getModule(array $config, string $moduleName, string $type = 'modern')
    {
        $devMode = Craft::$app->getConfig()->getGeneral()->devMode;
        $isHot = ($devMode && $config['useDevServer']);
        $manifest = null;
        // Try to get the manifest
        while ($manifest === null) {
            $manifestPath = $isHot
                ? $config['devServer']['manifestPath']
                : realpath(CRAFT_BASE_PATH) . ltrim($config['basePath'], '.');
            $manifest = self::getManifestFile($config['manifest'][$type], $manifestPath);
            // If the manigest isn't found, and it was hot, fall back on non-hot
            if ($manifest === null) {
                if ($isHot) {
                    $isHot = false;
                } else {
                    return null;
                }
            }
        }
        $module = $manifest[$moduleName];
        $prefix = $isHot
            ? $config['devServer']['publicPath']
            : $config['server']['publicPath'];
        if ($prefix !== '') {
            $module = rtrim($prefix, '/').'/'.ltrim($module, '/');
        }

        return $module;
    }

    // Public Static Methods
    // =========================================================================

    /**
     * @param string $name
     * @param string $path
     *
     * @return mixed
     */
    protected static function getManifestFile(string $name, string $path)
    {
        // Normalize the path, and use it for the cache key
        if ($path !== '') {
            $path = rtrim($path, '/').'/'.ltrim($name, '/');
        }
        // Return the memoized manifest if it exists
        if (!empty(self::$manifests[$path])) {
            return self::$manifests[$path];
        }
        // Create the dependency tags
        $dependency = new TagDependency([
            'tags' => [
                self::CACHE_TAG,
                self::CACHE_TAG.$path,
            ],
        ]);
        // Set the cache duraction based on devMode
        $cacheDuration = Craft::$app->getConfig()->getGeneral()->devMode
            ? self::DEVMODE_CACHE_DURATION
            : null;
        // Get the result from the cache, or parse the file
        $cache = Craft::$app->getCache();
        $manifest = $cache->getOrSet(
            self::CACHE_KEY.$path,
            function () use ($path) {
                $result = null;
                $string = @file_get_contents($path);
                if ($string) {
                    $result = JsonHelper::decodeIfJson($string);
                }

                return $result;
            },
            $cacheDuration,
            $dependency
        );
        self::$manifests[$path] = $manifest;

        return $manifest;
    }
}
