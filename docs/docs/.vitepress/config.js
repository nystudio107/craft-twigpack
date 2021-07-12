module.exports = {
    title: 'Twigpack Plugin Documentation',
    description: 'Documentation for the Twigpack plugin',
    base: '/docs/twigpack/',
    lang: 'en-US',
    head: [
        ['meta', { content: 'https://github.com/nystudio107', property: 'og:see_also', }],
        ['meta', { content: 'https://www.youtube.com/channel/UCOZTZHQdC-unTERO7LRS6FA', property: 'og:see_also', }],
        ['meta', { content: 'https://www.facebook.com/newyorkstudio107', property: 'og:see_also', }],
    ],
    themeConfig: {
        repo: 'nystudio107/craft-twigpack',
        docsDir: 'docs/docs',
        docsBranch: 'v1',
        algolia: {
            apiKey: '',
            indexName: 'twigpack'
        },
        editLinks: true,
        editLinkText: 'Edit this page on GitHub',
        lastUpdated: 'Last Updated',
        sidebar: 'auto',
    },
};
