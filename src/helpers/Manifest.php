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

use craft\helpers\FileHelper;
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
            $lines[] = "<script nomodule src=\"{$legacyModule}\"></script";
        } else {
            $lines[] = "<script src=\"{$legacyModule}\"></script";
        }

        return implode("\r\n", $lines);
    }

    /**
     * @return string
     */
    public function getModuleFixJs(): string
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
        $manifest = self::getManifestFile($config['manifest'][$type], $config['basePath']);
        if ($manifest === null) {
            return null;
        }
        $module = $manifest[$moduleName];
        $prefix = $config['useDevServer']
            ? $config['devServer']['basePath']
            : $config['server']['basePath'];
        $module = rtrim($prefix, '/') . '/' . $module;

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
        $path = FileHelper::normalizePath($path) . DIRECTORY_SEPARATOR . $name;
        // Return the memoized manifest if it exists
        if (self::$manifests[$path] !== null) {
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
