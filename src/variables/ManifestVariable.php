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
     * @param string     $moduleName
     * @param bool       $async
     * @param null|array $config
     *
     * @return \Twig_Markup
     * @throws \yii\web\NotFoundHttpException
     */
    public function includeCssModule(string $moduleName, bool $async = false, $config = null): \Twig_Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getCssModuleTags($moduleName, $async, $config)
        );
    }

    /**
     * Returns the CSS file in $path wrapped in <style></style> tags
     *
     * @param string $path
     *
     * @return string
     */
    public function includeInlineCssTags(string $path): \Twig_Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getCssInlineTags($path)
        );
    }

    /**
     * Returns the Critical CSS file for $template wrapped in <style></style> tags
     *
     * @param null|string $name
     * @param null|array $config
     *
     * @return \Twig_Markup
     */
    public function includeCriticalCssTags($name = null, $config = null): \Twig_Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getCriticalCssTags($name, $config)
        );
    }

    /**
     * Returns the uglified loadCSS rel=preload Polyfill as per:
     * https://github.com/filamentgroup/loadCSS#how-to-use-loadcss-recommended-example
     *
     * @return \Twig_Markup
     */
    public static function includeCssRelPreloadPolyfill(): \Twig_Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getCssRelPreloadPolyfill()
        );
    }

    /**
     * @param string     $moduleName
     * @param bool       $async
     * @param null|array $config
     *
     * @return null|\Twig_Markup
     * @throws \yii\web\NotFoundHttpException
     */
    public function includeJsModule(string $moduleName, bool $async = false, $config = null)
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getJsModuleTags($moduleName, $async, $config)
        );
    }

    /**
     * Return the URI to a module
     *
     * @param string $moduleName
     * @param string $type
     * @param null   $config
     *
     * @return null|\Twig_Markup
     * @throws \yii\web\NotFoundHttpException
     */
    public function getModuleUri(string $moduleName, string $type = 'modern', $config = null)
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getModule($moduleName, $type, $config)
        );
    }

    /**
     * Include the Safari 10.1 nomodule fix JavaScript
     *
     * @return \Twig_Markup
     */
    public function includeSafariNomoduleFix(): \Twig_Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getSafariNomoduleFix()
        );
    }

    /**
     * Returns the contents of a file from a URI path
     *
     * @param string $path
     *
     * @return \Twig_Markup
     */
    public function includeFile(string $path): \Twig_Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getFile($path)
        );
    }
}
