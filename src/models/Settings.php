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

namespace nystudio107\twigpack\models;

use craft\base\Model;

/**
 * @author    nystudio107
 * @package   Twigpack
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var bool If `devMode` is on, use webpack-dev-server to all for HMR (hot
     *      module reloading)
     */
    public $useDevServer = true;

    /**
     * @var bool If true, enforces Absolute Urls, if false, allows relative
     */
    public $useAbsoluteUrl = true;

    /**
     * @var string The JavaScript entry from the manifest.json to inject on
     *      Twig error pages
     */
    public $errorEntry = '';

    /**
     * @var string String to be appended to the cache key
     */
    public $cacheKeySuffix = '';

    /**
     * @var array Manifest file names
     */
    public $manifest = [
        'legacy' => 'manifest-legacy.json',
        'modern' => 'manifest.json',
    ];

    /**
     * @var array Public server config
     */
    public $server = [
        'manifestPath' => '/',
        'publicPath' => '/',
    ];

    /**
     * @var array webpack-dev-server config
     */
    public $devServer = [
        'manifestPath' => 'http://localhost:8080/',
        'publicPath' => 'http://localhost:8080/',
    ];

    /**
     * @var string defines which bundle will be used from the webpack dev server.
     *      Can be 'modern', 'legacy' or 'combined'. Defaults to 'modern'.
     */
    public $devServerBuildType = 'modern';

    /**
     * @var int defines for how many seconds the manifest file from the webpack dev server is cached.
     *      Any integer greater than 0 is valid. Defaults to 1.
     */
    public $devServerManifestCacheDuration = 1;

    /**
     * @var string Whether to include a Content Security Policy "nonce" for inline
     *      CSS or JavaScript. Valid values are 'header' or 'tag' for how the CSP
     *      should be included. c.f.:
     *      https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src#Unsafe_inline_script
     */
    public $cspNonce = '';

    /**
     * @var array Local files config
     */
    public $localFiles = [
        'basePath' => '@webroot/',
        'criticalPrefix' => 'dist/criticalcss/',
        'criticalSuffix' => '_critical.min.css',
    ];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['useDevServer', 'boolean'],
            ['useDevServer', 'default', 'value' => true],
            ['errorEntry', 'string'],
            ['devServerBuildType', 'string'],
            ['devServerManifestCacheDuration', 'number', 'integerOnly' => true, 'min' => 1 ],
            ['cspNonce', 'string'],
            [
                [
                    'manifest',
                    'server',
                    'devServer',
                    'localFiles',
                ],
                'each',
                'rule' => ['string'],
            ],
        ];
    }
}
