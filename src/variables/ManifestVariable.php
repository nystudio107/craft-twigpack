<?php
/**
 * Twigpack plugin for Craft CMS 3.x
 *
 * Twigpack is the conduit between Twig and webpack, with manifest.json & webpack-dev-server HMR support
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\twigpack\variables;

use nystudio107\twigpack\Twigpack;

use craft\helpers\Template;

/**
 * @author    nystudio107
 * @package   Twigpack
 * @since     1.0.0
 */
class ManifestVariable
{
    // Public Methods
    // =========================================================================

    /**
     * @param string $moduleName
     * @param bool   $async
     * @param null|array   $config
     *
     * @return \Twig_Markup
     */
    public function includeCssModule(string $moduleName, bool $async = false, $config = null): \Twig_Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getCssModuleTags($moduleName, $async, $config)
        );
    }

    /**
     * @param string $moduleName
     * @param bool   $async
     * @param null|array   $config
     *
     * @return \Twig_Markup
     */
    public function includeJsModule(string $moduleName, bool $async = false, $config = null): \Twig_Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getJsModuleTags($moduleName, $async, $config)
        );
    }

    /**
     * @return \Twig_Markup
     */
    public function includeSafariNomoduleFix(): \Twig_Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getSafariNomoduleFix()
        );
    }
}
