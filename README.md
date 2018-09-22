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

You can also install SEOmatic via the **Plugin Store** in the Craft AdminCP.

## Twigpack Overview

Twigpack is a bridge between Twig and webpack, with `manifest.json` & [webpack-dev-server](https://github.com/webpack/webpack-dev-server) hot module replacement (HMR) support.  
 
 Twigpack supports both modern and legacy bundle builds, as per the [Deploying ES2015+ Code in Production Today](https://philipwalton.com/articles/deploying-es2015-code-in-production-today/) article.
 
 Twigpack also handles generating the necessary tags to support both synchronous and asynchronous loading of JavaScripts and CSS.
 
 Additionally, Twigpack has a caching layer to ensure optimal performance.

## Configuring Twigpack

-Insert text here-

## Using Twigpack

Why not just use the popular [AssetRev plugin](https://github.com/clubstudioltd/craft-asset-rev)? You can, and we've used it in the past. But there were a few features

## Twigpack Roadmap

Some things to do, and ideas for potential features:

* Release it

Brought to you by [nystudio107](https://nystudio107.com/)
