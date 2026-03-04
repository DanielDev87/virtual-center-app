const mix = require('laravel-mix');

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

mix.js('resources/js/app.js', 'public/js')
   .css('resources/css/app.css', 'public/css')
   .options({
       processCssUrls: false
   })
   .version();

// Copy images
mix.copyDirectory('resources/img', 'public/img');

// Browser sync for development
if (mix.inProduction()) {
    mix.version();
} else {
    mix.browserSync({
        proxy: 'localhost:8000',
        files: [
            'resources/views/**/*.blade.php',
            'resources/js/**/*.js',
            'resources/css/**/*.css',
            'public/js/**/*.js',
            'public/css/**/*.css'
        ]
    });
}



