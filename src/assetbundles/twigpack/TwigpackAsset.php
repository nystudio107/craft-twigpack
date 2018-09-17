<?php
/**
 * Twigpack plugin for Craft CMS 3.x
 *
 * Twigpack is the conduit between Twig and webpack, with manifest.json & webpack-dev-server HMR support
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\twigpack\assetbundles\Twigpack;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    nystudio107
 * @package   Twigpack
 * @since     1.0.0
 */
class TwigpackAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@nystudio107/twigpack/assetbundles/twigpack/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Twigpack.js',
        ];

        $this->css = [
            'css/Twigpack.css',
        ];

        parent::init();
    }
}
