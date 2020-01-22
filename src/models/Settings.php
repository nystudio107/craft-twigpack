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
