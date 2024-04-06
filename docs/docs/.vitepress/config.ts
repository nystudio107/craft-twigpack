import {defineConfig} from 'vitepress'

export default defineConfig({
  title: 'Twigpack Plugin',
  description: 'Documentation for the Twigpack plugin',
  base: '/docs/twigpack/v4/',
  lang: 'en-US',
  head: [
    ['meta', {content: 'https://github.com/nystudio107', property: 'og:see_also',}],
    ['meta', {content: 'https://twitter.com/nystudio107', property: 'og:see_also',}],
    ['meta', {content: 'https://youtube.com/nystudio107', property: 'og:see_also',}],
    ['meta', {content: 'https://www.facebook.com/newyorkstudio107', property: 'og:see_also',}],
  ],
  themeConfig: {
    socialLinks: [
      {icon: 'github', link: 'https://github.com/nystudio107'},
      {icon: 'twitter', link: 'https://twitter.com/nystudio107'},
    ],
    logo: '/img/plugin-logo.svg',
    editLink: {
      pattern: 'https://github.com/nystudio107/craft-twigpack/edit/develop-v4/docs/docs/:path',
      text: 'Edit this page on GitHub'
    },
    algolia: {
      appId: '4Q1XLYYORS',
      apiKey: '62bbd46c6e5480c2fa8f8eda43a427fd',
      indexName: 'twigpack',
      searchParameters: {
        facetFilters: ["version:v4"],
      },
    },
    lastUpdatedText: 'Last Updated',
    sidebar: [],
    nav: [
      {text: 'Home', link: 'https://nystudio107.com/plugins/twigpack'},
      {text: 'Store', link: 'https://plugins.craftcms.com/twigpack'},
      {text: 'Changelog', link: 'https://nystudio107.com/plugins/twigpack/changelog'},
      {text: 'Issues', link: 'https://github.com/nystudio107/craft-twigpack/issues'},
      {
        text: 'v4', items: [
          {text: 'v5', link: 'https://nystudio107.com/docs/twigpack/'},
          {text: 'v4', link: '/'},
          {text: 'v1', link: 'https://nystudio107.com/docs/twigpack/v1/'},
        ],
      },
    ]
  },
});
