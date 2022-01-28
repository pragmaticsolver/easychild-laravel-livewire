const mix = require('laravel-mix')
const tailwindcss = require('tailwindcss')

const ReplaceInFileWebpackPlugin = require('replace-in-file-webpack-plugin')

mix.webpackConfig(webpack => {
    return {
        plugins: [
            new ReplaceInFileWebpackPlugin([
                {
                    dir: 'public',
                    files: ['sw.js'],
                    rules: [
                        {
                            search: 'SW_CACHE_VERSION',
                            replace: function() {
                                return Date.now()
                            },
                        },
                    ],
                },
            ]),
        ],
    }
})

mix.copyDirectory('resources/img', 'public/img')
    .copyDirectory('resources/fonts', 'public/fonts')
    .copyDirectory('resources/siteroot', 'public')
    .js('resources/js/app.js', 'public/js/app.js')
    .js('resources/js/sw.js', 'public/sw.js')
    .sass('resources/sass/app.scss', 'public/css/app.css')
    .options({
        processCssUrls: false,
        postCss: [tailwindcss('./tailwind.config.js')],
    })

if (mix.inProduction()) {
    mix.version()
}
