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

namespace nystudio107\twigpack\helpers;

use nystudio107\twigpack\Twigpack;
use nystudio107\twigpack\models\Settings;

use Craft;
use craft\helpers\Html;
use craft\helpers\Json as JsonHelper;
use craft\helpers\UrlHelper;

use yii\base\Exception;
use yii\caching\ChainedDependency;
use yii\caching\FileDependency;
use yii\caching\TagDependency;
use yii\web\NotFoundHttpException;

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

    const CSP_HEADERS = [
        'Content-Security-Policy',
        'X-Content-Security-Policy',
        'X-WebKit-CSP',
    ];

    const SUPPRESS_ERRORS_FOR_MODULES = [
        'styles.js',
    ];

    // Protected Static Properties
    // =========================================================================

    /**
     * @var array
     */
    protected static $files;

    /**
     * @var bool
     */
    protected static $isHot = false;

    // Public Static Methods
    // =========================================================================

    /**
     * @param array $config
     * @param string $moduleName
     * @param bool $async
     * @param array $attributes additional HTML key/value pair attributes to add to the resulting tag
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public static function getCssModuleTags(array $config, string $moduleName, bool $async, array $attributes = []): string
    {
        $legacyModule = self::getModule($config, $moduleName, 'legacy', true);
        if ($legacyModule === null) {
            return '';
        }
        $lines = [];
        if ($async) {
            $lines[] = Html::cssFile($legacyModule, array_merge([
                'rel' => 'stylesheet',
                'media' => 'print',
                'onload' => "this.media='all'",
            ], $attributes));
            $lines[] = Html::cssFile($legacyModule, array_merge([
                'rel' => 'stylesheet',
                'noscript' => true,
            ], $attributes));
        } else {
            $lines[] = Html::cssFile($legacyModule, array_merge([
                'rel' => 'stylesheet',
            ], $attributes));
        }

        return implode("\r\n", $lines);
    }

    /**
     * @param string $path
     * @param array $attributes additional HTML key/value pair attributes to add to the resulting tag
     *
     * @return string
     */
    public static function getCssInlineTags(string $path, array $attributes = []): string
    {
        $result = self::getFile($path);
        if ($result) {
            $config = [];
            $nonce = self::getNonce();
            if ($nonce !== null) {
                $config['nonce'] = $nonce;
                self::includeNonce($nonce, 'style-src');
            }
            $result = Html::style($result, array_merge($config, $attributes));

            return $result;
        }

        return '';
    }

    /**
     * @param array $config
     * @param null|string $name
     * @param array $attributes additional HTML key/value pair attributes to add to the resulting tag
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     */
    public static function getCriticalCssTags(array $config, $name = null, array $attributes = []): string
    {
        // Resolve the template name
        $template = Craft::$app->getView()->resolveTemplate($name ?? Twigpack::$templateName ?? '');
        if ($template) {
            $name = self::combinePaths(
                pathinfo($template, PATHINFO_DIRNAME),
                pathinfo($template, PATHINFO_FILENAME)
            );
            $dirPrefix = 'templates/';
            if (defined('CRAFT_TEMPLATES_PATH')) {
                $dirPrefix = CRAFT_TEMPLATES_PATH;
            }
            $name = strstr($name, $dirPrefix);
            $name = (string)str_replace($dirPrefix, '', $name);
            $path = self::combinePaths(
                    $config['localFiles']['basePath'],
                    $config['localFiles']['criticalPrefix'],
                    $name
                ) . $config['localFiles']['criticalSuffix'];

            return self::getCssInlineTags($path, $attributes);
        }

        return '';
    }

    /**
     * Returns the uglified loadCSS rel=preload Polyfill as per:
     * https://github.com/filamentgroup/loadCSS#how-to-use-loadcss-recommended-example
     *
     * @return string
     * @throws \craft\errors\DeprecationException
     * @deprecated in 1.2.0
     */
    public static function getCssRelPreloadPolyfill(): string
    {
        Craft::$app->getDeprecator()->log('craft.twigpack.includeCssRelPreloadPolyfill()', 'craft.twigpack.includeCssRelPreloadPolyfill() has been deprecated, this function now does nothing. You can safely remove craft.twigpack.includeCssRelPreloadPolyfill() from your templates.');

        return '';
    }

    /**
     * @param array $config
     * @param string $moduleName
     * @param bool $async
     * @param array $attributes additional HTML key/value pair attributes to add to the resulting tag
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    public static function getJsModuleTags(array $config, string $moduleName, bool $async, array $attributes = [])
    {
        $legacyModule = self::getModule($config, $moduleName, 'legacy', true);
        if ($legacyModule === null) {
            return '';
        }
        $modernModule = '';
        if ($async) {
            $modernModule = self::getModule($config, $moduleName, 'modern', true);
            if ($modernModule === null) {
                return '';
            }
        }
        $lines = [];
        if ($async) {
            $lines[] = Html::jsFile($modernModule, array_merge([
                'type' => 'module',
            ], $attributes));
            $lines[] = Html::jsFile($legacyModule, array_merge([
                'nomodule' => true,
            ], $attributes));
        } else {
            $lines[] = Html::jsFile($legacyModule, array_merge([
            ], $attributes));
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
     * @param array $attributes additional HTML key/value pair attributes to add to the resulting tag
     *
     * @return string
     */
    public static function getSafariNomoduleFix(array $attributes = []): string
    {
        $code = /** @lang JavaScript */
            <<<EOT
!function(){var e=document,t=e.createElement("script");if(!("noModule"in t)&&"onbeforeload"in t){var n=!1;e.addEventListener("beforeload",function(e){if(e.target===t)n=!0;else if(!e.target.hasAttribute("nomodule")||!n)return;e.preventDefault()},!0),t.type="module",t.src=".",e.head.appendChild(t),t.remove()}}();
EOT;
        $config = [];
        $nonce = self::getNonce();
        if ($nonce !== null) {
            $config['nonce'] = $nonce;
            self::includeNonce($nonce, 'script-src');
        }

        return Html::script($code, array_merge($config, $attributes));
    }

    /**
     * Return the URI to a module
     *
     * @param array $config
     * @param string $moduleName
     * @param string $type
     * @param bool $soft
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    public static function getModule(array $config, string $moduleName, string $type = 'modern', bool $soft = false)
    {
        // Get the module entry
        $module = self::getModuleEntry($config, $moduleName, $type, $soft);
        if ($module !== null) {
            $prefix = self::$isHot
                ? $config['devServer']['publicPath']
                : $config['server']['publicPath'];
            $useAbsoluteUrl = $config['useAbsoluteUrl'];
            // If the module isn't a full URL, prefix it as required
            if ($useAbsoluteUrl && !UrlHelper::isAbsoluteUrl($module)) {
                $module = self::combinePaths($prefix, $module);
            }
            // Resolve any aliases
            $alias = Craft::getAlias($module, false);
            if ($alias) {
                $module = $alias;
            }
            // Make sure it's a full URL, as required
            if ($useAbsoluteUrl && !UrlHelper::isAbsoluteUrl($module) && !is_file($module)) {
                try {
                    $module = UrlHelper::siteUrl($module);
                } catch (Exception $e) {
                    Craft::error($e->getMessage(), __METHOD__);
                }
            }
        }

        return $module;
    }

    /**
     * Return the HASH value from to module
     *
     * @param array $config
     * @param string $moduleName
     * @param string $type
     * @param bool $soft
     *
     * @return null|string
     */
    public static function getModuleHash(array $config, string $moduleName, string $type = 'modern', bool $soft = false)
    {

        $moduleHash = '';
        try {
            // Get the module entry
            $module = self::getModuleEntry($config, $moduleName, $type, $soft);
            if ($module !== null) {
                // Extract only the Hash Value
                $modulePath = pathinfo($module);
                $moduleFilename = $modulePath['filename'];
                $moduleHash = substr($moduleFilename, strpos($moduleFilename, ".") + 1);
            }
        } catch (Exception $e) {
            // return empty string if no module is found
            return '';
        }

        return $moduleHash;
    }

    /**
     * Return a module's raw entry from the manifest
     *
     * @param array $config
     * @param string $moduleName
     * @param string $type
     * @param bool $soft
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    public static function getModuleEntry(
        array $config,
        string $moduleName,
        string $type = 'modern',
        bool $soft = false
    )
    {
        $module = null;
        // Get the manifest file
        $manifest = self::getManifestFile($config, $type);
        if ($manifest !== null) {
            // Make sure it exists in the manifest
            if (empty($manifest[$moduleName])) {
                // Don't report errors for any files in SUPPRESS_ERRORS_FOR_MODULES
                if (!in_array($moduleName, self::SUPPRESS_ERRORS_FOR_MODULES)) {
                    self::reportError(Craft::t(
                        'twigpack',
                        'Module does not exist in the manifest: {moduleName}',
                        ['moduleName' => $moduleName]
                    ), $soft);
                }

                return null;
            }
            $module = $manifest[$moduleName];
        }

        return $module;
    }

    /**
     * Return a JSON-decoded manifest file
     *
     * @param array $config
     * @param string $type
     *
     * @return null|array
     * @throws NotFoundHttpException
     */
    public static function getManifestFile(array $config, string $type = 'modern')
    {
        $manifest = null;
        // Determine whether we should use the devServer for HMR or not
        $devMode = Craft::$app->getConfig()->getGeneral()->devMode;
        self::$isHot = ($devMode && $config['useDevServer']);
        // Try to get the manifest
        while ($manifest === null) {
            $manifestPath = self::$isHot
                ? $config['devServer']['manifestPath']
                : $config['server']['manifestPath'];
            // If this is a dev-server, use the defined build type
            $thisType = $type;
            if (self::$isHot) {
                $thisType = $config['devServerBuildType'] === 'combined'
                    ? $thisType
                    : $config['devServerBuildType'];
            }
            // Normalize the path
            $path = self::combinePaths($manifestPath, $config['manifest'][$thisType]);
            $manifest = self::getJsonFile($path, $config['devServerManifestCacheDuration'], true);
            // If the manifest isn't found, and it was hot, fall back on non-hot
            if ($manifest === null) {
                // We couldn't find a manifest; throw an error
                self::reportError(Craft::t(
                    'twigpack',
                    'Manifest file not found at: {manifestPath}',
                    ['manifestPath' => $manifestPath]
                ), true);
                if (self::$isHot) {
                    // Try again, but not with home module replacement
                    self::$isHot = false;
                } else {
                    // Give up and return null
                    return null;
                }
            }
        }

        return $manifest;
    }

    /**
     * Returns the contents of a file from a URI path
     *
     * @param string $path
     *
     * @return string
     */
    public static function getFile(string $path): string
    {
        return self::getFileFromUri($path, null, true) ?? '';
    }

    /**
     * @param array $config
     * @param string $fileName
     * @param string $type
     *
     * @return string
     */
    public static function getFileFromManifest(array $config, string $fileName, string $type = 'legacy'): string
    {
        $path = null;
        try {
            $path = self::getModuleEntry($config, $fileName, $type, true);
        } catch (NotFoundHttpException $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }
        if ($path !== null) {
            // Determine whether we should use the devServer for HMR or not
            $devMode = Craft::$app->getConfig()->getGeneral()->devMode;
            if ($devMode) {
                $devServerPrefix = $config['devServer']['publicPath'];
                // If we're using the devserver, swap in the deverserver path
                if (UrlHelper::isAbsoluteUrl($path) && self::$isHot) {
                    $path = parse_url($path, PHP_URL_PATH);
                }
                $devServerPath = self::combinePaths(
                    $devServerPrefix,
                    $path
                );
                $devServerFile = self::getFileFromUri($devServerPath, null);
                if ($devServerFile) {
                    return $devServerFile;
                }
            }
            // Otherwise, try not-hot files
            $localPrefix = $config['localFiles']['basePath'];
            $localPath = self::combinePaths(
                $localPrefix,
                $path
            );
            $alias = Craft::getAlias($localPath, false);
            if ($alias && is_string($alias)) {
                $localPath = $alias;
            }
            if (is_file($localPath)) {
                return self::getFile($localPath) ?? '';
            }
        }

        return '';
    }

    /**
     * Invalidate all of the manifest caches
     */
    public static function invalidateCaches()
    {
        $cache = Craft::$app->getCache();
        TagDependency::invalidate($cache, self::CACHE_TAG);
        Craft::info('All manifest caches cleared', __METHOD__);
    }

    /**
     * Return the contents of a JSON file from a URI path
     *
     * @param string $path
     * @param int $devCacheDuration
     * @param bool $isManifest
     *
     * @return null|array
     */
    protected static function getJsonFile(string $path, int $devCacheDuration = 1, bool $isManifest = false)
    {
        return self::getFileFromUri($path, [self::class, 'jsonFileDecode'], false, $devCacheDuration, $isManifest);
    }

    // Protected Static Methods
    // =========================================================================

    /**
     * Return the contents of a file from a URI path
     *
     * @param string $path
     * @param callable|null $callback
     * @param bool $pathOnly
     * @param int $devCacheDuration
     * @param bool $isManifest
     *
     * @return null|mixed
     */
    protected static function getFileFromUri(string $path, callable $callback = null, bool $pathOnly = false, int $devCacheDuration = 1, bool $isManifest = false)
    {
        // Resolve any aliases
        $alias = Craft::getAlias($path, false);
        if ($alias && is_string($alias)) {
            $path = $alias;
        }
        // If we only want the file via path, make sure it exists
        if ($pathOnly && !is_file($path)) {
            Craft::warning(Craft::t(
                'twigpack',
                'File does not exist: {path}',
                ['path' => $path]
            ), __METHOD__);

            return '';
        }
        // Make sure it's a full URL
        if (!UrlHelper::isAbsoluteUrl($path) && !is_file($path)) {
            try {
                $path = UrlHelper::siteUrl($path);
            } catch (Exception $e) {
                Craft::error($e->getMessage(), __METHOD__);
            }
        }

        return self::getFileContents($path, $callback, $devCacheDuration, $isManifest);
    }

    /**
     * Return the contents of a file from the passed in path
     *
     * @param string $path
     * @param callable $callback
     * @param int $devCacheDuration
     * @param bool $isManifest
     *
     * @return null|mixed
     */
    protected static function getFileContents(string $path, callable $callback = null, int $devCacheDuration = 1, bool $isManifest = false)
    {
        // Return the memoized manifest if it exists
        if (!empty(self::$files[$path])) {
            return self::$files[$path];
        }
        // Create the dependency tags
        $dependency = new TagDependency([
            'tags' => [
                self::CACHE_TAG,
                self::CACHE_TAG . $path,
            ],
        ]);
        // If this is a file path such as for the `manifest.json`, add a FileDependency so it's cache bust if the file changes
        if (!UrlHelper::isAbsoluteUrl($path)) {
            $dependency = new ChainedDependency([
                'dependencies' => [
                    new FileDependency([
                        'fileName' => $path
                    ]),
                    $dependency
                ]
            ]);
        }
        // Set the cache duration based on devMode
        $cacheDuration = Craft::$app->getConfig()->getGeneral()->devMode
            ? $devCacheDuration
            : null;
        // If we're in `devMode` invalidate the cache immediately
        if (!$isManifest && Craft::$app->getConfig()->getGeneral()->devMode) {
            self::invalidateCaches();
        }
        // Get the result from the cache, or parse the file
        $cache = Craft::$app->getCache();
        $settings = Twigpack::$plugin->getSettings();
        $cacheKeySuffix = $settings->cacheKeySuffix ?? '';
        $file = $cache->getOrSet(
            self::CACHE_KEY . $cacheKeySuffix . $path,
            function () use ($path, $callback) {
                $result = null;
                $contents = null;
                if (UrlHelper::isAbsoluteUrl($path)) {
                    /**
                     * Silly work-around for what appears to be a file_get_contents bug with https
                     * http://stackoverflow.com/questions/10524748/why-im-getting-500-error-when-using-file-get-contents-but-works-in-a-browser
                     */
                    $opts = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ],
                        'http' => [
                            'timeout' => 5,
                            'ignore_errors' => true,
                            'header' => "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13\r\n",
                        ],
                    ];
                    $context = stream_context_create($opts);
                    if (self::getHttpResponseCode($path, $context) === '200') {
                        $contents = @file_get_contents($path, false, $context);
                    }
                } else {
                    $contents = @file_get_contents($path);
                }
                if ($contents) {
                    $result = $contents;
                    if ($callback) {
                        $result = $callback($result);
                    }
                }

                return $result;
            },
            $cacheDuration,
            $dependency
        );
        self::$files[$path] = $file;

        return $file;
    }

    /**
     * Get the response code from a given $url
     *
     * @param $url
     * @param $context
     * @return false|string
     */
    protected static function getHttpResponseCode($url, $context)
    {
        $headers = @get_headers($url, 0, $context);
        if (empty($headers)) {
            return '404';
        }

        return substr($headers[0], 9, 3);
    }

    /**
     * Combined the passed in paths, whether file system or URL
     *
     * @param string ...$paths
     *
     * @return string
     */
    protected static function combinePaths(string ...$paths): string
    {
        $last_key = count($paths) - 1;
        array_walk($paths, function (&$val, $key) use ($last_key) {
            switch ($key) {
                case 0:
                    $val = rtrim($val, '/ ');
                    break;
                case $last_key:
                    $val = ltrim($val, '/ ');
                    break;
                default:
                    $val = trim($val, '/ ');
                    break;
            }
        });

        $first = array_shift($paths);
        $last = array_pop($paths);
        $paths = array_filter($paths);
        array_unshift($paths, $first);
        $paths[] = $last;

        return implode('/', $paths);
    }

    /**
     * @param string $error
     * @param bool $soft
     *
     * @throws NotFoundHttpException
     */
    protected static function reportError(string $error, $soft = false)
    {
        $devMode = Craft::$app->getConfig()->getGeneral()->devMode;
        if ($devMode && !$soft) {
            throw new NotFoundHttpException($error);
        }
        if (self::$isHot) {
            Craft::warning($error, __METHOD__);
        } else {
            Craft::error($error, __METHOD__);
        }
    }

    // Private Static Methods
    // =========================================================================

    /**
     * @param string $nonce
     * @param string $cspDirective
     */
    private static function includeNonce(string $nonce, string $cspDirective)
    {
        $cspNonceType = self::getCspNonceType();
        if ($cspNonceType) {
            $cspValue = "{$cspDirective} 'nonce-$nonce'";
            foreach(self::CSP_HEADERS as $cspHeader) {
                switch ($cspNonceType) {
                    case 'tag':
                        Craft::$app->getView()->registerMetaTag([
                            'httpEquiv' => $cspHeader,
                            'value' => $cspValue,
                        ]);
                        break;
                    case 'header':
                        Craft::$app->getResponse()->getHeaders()->add($cspHeader, $cspValue . ';');
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * @return string|null
     */
    private static function getCspNonceType()
    {
        /** @var Settings $settings */
        $settings = Twigpack::$plugin->getSettings();
        $cspNonceType = !empty($settings->cspNonce) ? strtolower($settings->cspNonce) : null;

        return $cspNonceType;
    }

    /**
     * @return string|null
     */
    private static function getNonce()
    {
        $result = null;
        if (self::getCspNonceType() !== null) {
            try {
                $result = bin2hex(random_bytes(22));
            } catch (\Exception $e) {
                // That's okay
            }
        }

        return $result;
    }
    /**
     * @param $string
     *
     * @return null|array
     */
    private static function jsonFileDecode($string)
    {
        $json = JsonHelper::decodeIfJson($string);
        if (is_string($json)) {
            Craft::error('Error decoding JSON file: ' . $json, __METHOD__);
            $json = null;
        }

        return $json;
    }
}
