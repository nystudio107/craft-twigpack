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

use yii\web\NotFoundHttpException;

use Twig\Markup;

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
     * Returns the uglified loadCSS rel=preload Polyfill as per:
     * https://github.com/filamentgroup/loadCSS#how-to-use-loadcss-recommended-example
     *
     * @return Markup
     */
    public static function includeCssRelPreloadPolyfill(): Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getCssRelPreloadPolyfill() ?? ''
        );
    }

    /**
     * @param string $moduleName
     * @param bool $async
     * @param array $attributes additional HTML key/value pair attributes to add to the resulting tag
     *
     * @return Markup
     * @throws NotFoundHttpException
     */
    public function includeCssModule(string $moduleName, bool $async = false, array $attributes = []): Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getCssModuleTags($moduleName, $async, null, $attributes) ?? ''
        );
    }

    /**
     * Returns the CSS file in $path wrapped in <style></style> tags
     *
     * @param string $path
     * @param array $attributes additional HTML key/value pair attributes to add to the resulting tag
     *
     * @return Markup
     */
    public function includeInlineCssTags(string $path, array $attributes = []): Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getCssInlineTags($path, $attributes) ?? ''
        );
    }

    /**
     * Returns the Critical CSS file for $template wrapped in <style></style>
     * tags
     *
     * @param null|string $name
     * @param array $attributes additional HTML key/value pair attributes to add to the resulting tag
     *
     * @return Markup
     * @throws \Twig\Error\LoaderError
     */
    public function includeCriticalCssTags($name = null, array $attributes = []): Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getCriticalCssTags($name, null, $attributes) ?? ''
        );
    }

    /**
     * @param string     $moduleName
     * @param bool       $async
     * @param array $attributes additional HTML key/value pair attributes to add to the resulting tag
     *
     * @return null|Markup
     * @throws NotFoundHttpException
     */
    public function includeJsModule(string $moduleName, bool $async = false, array $attributes = [])
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getJsModuleTags($moduleName, $async, null, $attributes) ?? ''
        );
    }

    /**
     * Return the URI to a module
     *
     * @param string $moduleName
     * @param string $type
     * @param null   $config
     *
     * @return null|Markup
     * @throws NotFoundHttpException
     */
    public function getModuleUri(string $moduleName, string $type = 'modern', $config = null)
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getModule($moduleName, $type, $config) ?? ''
        );
    }

    /**
     * Return the HASH value from a module
     *
     * @param string $moduleName
     * @param string $type
     * @param null   $config
     *
     * @return null|Markup
     * @throws NotFoundHttpException
     */
    public function getModuleHash(string $moduleName, string $type = 'modern', $config = null)
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getModuleHash($moduleName, $type, $config) ?? ''
        );
    }

    /**
     * Include the Safari 10.1 nomodule fix JavaScript
     *
     * @param array $attributes additional HTML key/value pair attributes to add to the resulting tag
     *
     * @return Markup
     */
    public function includeSafariNomoduleFix(array $attributes = []): Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getSafariNomoduleFix($attributes) ?? ''
        );
    }

    /**
     * Returns the contents of a file from a URI path
     *
     * @param string $path
     *
     * @return Markup
     */
    public function includeFile(string $path): Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getFile($path) ?? ''
        );
    }

    /**
     * Returns the contents of a file from the $fileName in the manifest
     *
     * @param string $fileName
     * @param string $type
     * @param null   $config
     *
     * @return Markup
     */
    public function includeFileFromManifest(string $fileName, string $type = 'legacy', $config = null): Markup
    {
        return Template::raw(
            Twigpack::$plugin->manifest->getFileFromManifest($fileName, $type, $config) ?? ''
        );
    }
}
