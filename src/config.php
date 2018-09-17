<?php
/**
 * Twigpack plugin for Craft CMS 3.x
 *
 * Twigpack is the conduit between Twig and webpack, with manifest.json & webpack-dev-server HMR support
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2018 nystudio107
 */

/**
 * Twigpack config.php
 *
 * This file exists only as a template for the Twigpack settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'twigpack.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    // File system path
    'basePath' => './web/dist/',
    // Manifest names
    'manifest' => [
        'legacy' => 'manifest-legacy.json',
        'modern' => 'manifest.json',
    ],
    // Public server config
    'server' => [
        'publicPath' => '/dist/',
    ],
    // If `devMode` is on, use webpack-dev-server to all for HMR (hot module reloading)
    'useDevServer' => true,
    // webpack-dev-server config
    'devServer' => [
        'publicPath' => 'http://localhost/dist/',
    ],
];
