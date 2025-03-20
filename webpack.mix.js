const mix = require('laravel-mix');

// Configure Vue
mix.vue({ version: 2 });

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
mix.sass('resources/sass/web/app.scss', 'public/assets/css')
   .options({processCssUrls: false});

mix.js('resources/js/web/app.js', 'public/assets/js');
      
// Dashboard
mix.js('resources/js/dashboard/app.js', 'public/assets/dashboard/js/bundle.administration.js');
mix.sass('resources/sass/dashboard/app.scss', 'public/assets/dashboard/css')
   .options({processCssUrls: false});

// Enable versioning for all assets
if (mix.inProduction()) {
    mix.version();
}
