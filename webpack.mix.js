const mix = require('laravel-mix');

mix.webpackConfig({
    resolve: {
        extensions: ['.js', '.vue', '.json'],
        alias: {
            //'vue$': 'vue/dist/vue.esm.js',
            '@': __dirname + '/resources/js/dashboard/',
        },
    },
});


/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// Web
mix.sass('resources/sass/web/app.scss', 'public/assets/css/app.css').options({processCssUrls: false}).version();
mix.js('resources/js/web/app.js', 'public/assets/js/app.js');
      
// Dashboard
mix.js('resources/js/dashboard/app.js', 'public/assets/dashboard/js/bundle.administration.js').version();
mix.sass('resources/sass/dashboard/app.scss', 'public/assets/dashboard/css/app.css').options({processCssUrls: false}).version();

