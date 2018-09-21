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
     * @var bool If `devMode` is on, use webpack-dev-server to all for HMR (hot module reloading)
     */
    public $useDevServer = true;

    // Manifest file names
    public $manifest = [
        'legacy' => 'manifest-legacy.json',
        'modern' => 'manifest.json',
    ];
    // Public server config
    public $server = [
        'manifestPath' => '/dist/',
        'publicPath' => '/',
    ];
    // webpack-dev-server config
    public $devServer = [
        'manifestPath' => 'http://127.0.0.1:8080',
        'publicPath' => 'http://192.168.10.10:8080/',
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
            [
                [
                    'manifest',
                    'server',
                    'devServer',
                ],
                'each',
                'rule' => ['string']
            ],
        ];
    }
}
