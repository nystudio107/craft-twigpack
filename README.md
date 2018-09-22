[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nystudio107/craft-twigpack/badges/quality-score.png?b=v1)](https://scrutinizer-ci.com/g/nystudio107/craft-twigpack/?branch=v1) [![Code Coverage](https://scrutinizer-ci.com/g/nystudio107/craft-twigpack/badges/coverage.png?b=v1)](https://scrutinizer-ci.com/g/nystudio107/craft-twigpack/?branch=v1) [![Build Status](https://scrutinizer-ci.com/g/nystudio107/craft-twigpack/badges/build.png?b=v1)](https://scrutinizer-ci.com/g/nystudio107/craft-twigpack/build-status/v1) [![Code Intelligence Status](https://scrutinizer-ci.com/g/nystudio107/craft-twigpack/badges/code-intelligence.svg?b=v1)](https://scrutinizer-ci.com/code-intelligence)

# Twigpack plugin for Craft CMS 3.x

Twigpack is a bridge between Twig and webpack, with manifest.json & webpack-dev-server HMR support

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require nystudio107/craft-twigpack

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Twigpack.

You can also install Twigpack via the **Plugin Store** in the Craft AdminCP.

## Twigpack Overview

Twigpack is a bridge between Twig and webpack, with `manifest.json` & [webpack-dev-server](https://github.com/webpack/webpack-dev-server) hot module replacement (HMR) support.
 
 Twigpack supports both modern and legacy bundle builds, as per the [Deploying ES2015+ Code in Production Today](https://philipwalton.com/articles/deploying-es2015-code-in-production-today/) article.
 
 Twigpack also handles generating the necessary `<script>` and `<link>` tags to support both synchronous and asynchronous loading of JavaScript and CSS.
 
 Additionally, Twigpack has a caching layer to ensure optimal performance.

### Why not just use AssetRev?

You might be wondering... why not just use the excellent [AssetRev plugin](https://github.com/clubstudioltd/craft-asset-rev)? You certainly can, and we have in the past. Twigpack was written because:
* We wanted support for legacy/modern JavaScript bundles
* We wanted to use `webpack-dev-server` for hot module replacement
* We wanted a performant caching mechanism in place
* ...and we also didn't care about various versioning schemes other than the webpack `manifest.json`

Use whatever works for you!

## Configuring Twigpack

Add configuration for Twigpack is done via the `config.php` config file. Here's the default `config.php`; it should be renamed to `twigpack.php` and copied to your `config/` directory to take effect.

### The `config.php` File

```php
return [
    // Global settings
    '*' => [
        // If `devMode` is on, use webpack-dev-server to all for HMR (hot module reloading)
        'useDevServer' => false,
        // Manifest file names
        'manifest' => [
            'legacy' => 'manifest-legacy.json',
            'modern' => 'manifest.json',
        ],
        // Public server config
        'server' => [
            'manifestPath' => '/',
            'publicPath' => '/',
        ],
        // webpack-dev-server config
        'devServer' => [
            'manifestPath' => 'http://localhost:8080/',
            'publicPath' => 'http://localhost:8080/',
        ],
    ],
    // Live (production) environment
    'live' => [
    ],
    // Staging (pre-production) environment
    'staging' => [
    ],
    // Local (development) environment
    'local' => [
        // If `devMode` is on, use webpack-dev-server to all for HMR (hot module reloading)
        'useDevServer' => true,
    ],
];
```

* **useDevServer** - is a `boolean` that sets whether you will be using [webpack-dev-server](https://github.com/webpack/webpack-dev-server) for hot module replacement (HMR)
* **manifest** - is an array with `legacy` and `modern` keys. If you're not using legacy/modern bundles, just name them both `manifest.json`
  * **legacy** - the name of your legacy manifest file
  * **modern** - the name of your modern manifest file
 * **server** - is an array with `manifestPath` and `publicPath` keys:
   * **manifestPath** - the public server path to your manifest files; it can be a full URL or a partial path.  This is usually the same as whatever you set your webpack `output.publicPath` to
   * **publicPath** - the public server path to your asset files; it can be a full URL or a partial path. This is usually the same as whatever you set your webpack `output.publicPath` to
 * **devServer** - is an array with `manifestPath` and `publicPath` keys:
   * **manifestPath** - the devServer path to your manifest files; it can be a full URL or a partial path.  This is usually the same as whatever you set your webpack `devServer.publicPath` to
   * **publicPath** - the devServer path to your asset files; it can be a full URL or a partial path. This is usually the same as whatever you set your webpack `output.publicPath` to

### Legacy and Modern Bundles

The idea behind using `manifest.json` and `manifest-legacy.json` is that there will be two builds, one for modern ES6+ modules, and a second for legacy ES5 bundles with polyfills, etc. The entry points are named the same, but the files the entry points load are different.

Even if you're not producing legacy and modern bundles as per the [Deploying ES2015+ Code in Production Today](https://philipwalton.com/articles/deploying-es2015-code-in-production-today/) article, you can still use Twigpack. Just name both the `legacy` and `modern` manifest files `manifest.json` in the `config.php`

### DevServer

If **useDevServer** is set to `true`, Twigpack will first try to find your manifest files via the **devServer** config. If that fails, it will fall back on your **server** config.

Note that the **devServer** will only be used if `devMode` is on.

Using the [webpack-dev-server](https://github.com/webpack/webpack-dev-server) means you get hot modules replacement, and the files are all built in-memory for speed. Think of it as a very enhanced version of BrowserWatch or `watch` tasks.

Even if you're not using `webpack-dev-server`, you can still use Twigpack. Just set **useDevServer** to false.

### Caching

Twigpack will memoize the manifest files for performance, and it will also cache them. If `devMode` is on, the cache duration is only 1 second.

If `devMode` is off, the files will be cached until Craft Template Caches are cleared (which is typically done via deployment), or Craft's Data Caches are cleared. You can also manually clear the cache by using the **Clear Caches** Utility.

## Using Twigpack

Here's a simplified example `manifest.json` file that we'll be using for these examples:

```json
{
    "style.css": "css/style.sfkjsf734ashf.css",
    "app.js": "js/app.gldlkg983ajhs8s.js"
}
```

### Including CSS

To include a versioned CSS file in your templates, do:

`{{ craft.twigpack.includeCssModule("style.css") }}`

This will output:

```html
<link rel="stylesheet" href="/css/style.sfkjsf734ashf.css" />
```

You can also include a second optional parameter, to determine whether the CSS should be loaded asynchronously or not (it defaults to `false`):

`{{ craft.twigpack.includeCssModule("style.css", true) }}`

This will output:

```html
<link rel="preload" href="/css/style.sfkjsf734ashf.css" as="style" onload="this.rel='stylesheet'" />
<noscript><link rel="stylesheet" href="/css/style.sfkjsf734ashf.css"></noscript>
```

### Including JavaScript

To include a versioned JavaScript module in your templates, do:

`{{ craft.twigpack.includeJsModule("app.js") }}`

This will output:

```html
<script src="/js/app.gldlkg983ajhs8s.js"></script>
```

You can also include a second optional parameter, to determine whether the JavaScript module should be loaded asynchronously or not (it defaults to `false`):

`{{ craft.twigpack.includeJsModule("app.js", true) }}`

This will output:

```html
<script type="module" src="/js/app.gldlkg983ajhs8s.js"></script>
<script nomodule src="/js/app-legacy.gldlkg983ajhs8s.js"></script>
```

This assumes you've set up a webpack build as per the [Deploying ES2015+ Code in Production Today](https://philipwalton.com/articles/deploying-es2015-code-in-production-today/) article, where you create both a legacy ES5 bundle with polyfills, and a modern ES6+ module.

There is a nomodule fix for Safari 10.1 that you can include on the page via:

`{{ craft.twigpack.includeSafariNomoduleFix() }}`

This will output:
```html
<script>
!function(){var e=document,t=e.createElement("script");if(!("noModule"in t)&&"onbeforeload"in t){var n=!1;e.addEventListener("beforeload",function(e){if(e.target===t)n=!0;else if(!e.target.hasAttribute("nomodule")||!n)return;e.preventDefault()},!0),t.type="module",t.src=".",e.head.appendChild(t),t.remove()}}();
</script>
```

...as per the [safari-nomodule.js Gist](https://gist.github.com/samthor/64b114e4a4f539915a95b91ffd340acc). You'll want to include this one on the page, before you do `{{ craft.twigpack.includeJsModule("app.js", true) }}`. It's only necessary if you're using legacy/modern JavaScript bundles.

## Twigpack Roadmap

Some things to do, and ideas for potential features:

* Release it

Brought to you by [nystudio107](https://nystudio107.com/)
