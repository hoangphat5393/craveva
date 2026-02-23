const mix = require("laravel-mix");

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

// LƯU Ý: User đã xác nhận có backup, build thẳng vào file production
mix.js("resources/js/app.js", "public/js/app.js")
    .js("resources/js/main.js", "public/js/main.js")
    .js("resources/js/custom.js", "public/js/custom.js")
    .sass("resources/sass/app.scss", "public/css/app.css");

// Uncomment if you have SASS/CSS files to compile
// mix.sass('resources/sass/app.scss', 'public/css');

// Copy SweetAlert2 assets
mix.copy('node_modules/sweetalert2/dist/sweetalert2.all.min.js', 'public/vendor/sweetalert/sweetalert2.all.min.js');
mix.copy('node_modules/sweetalert2/dist/sweetalert2.min.css', 'public/vendor/sweetalert/sweetalert2.min.css');

// Copy Quill assets
mix.copy('node_modules/quill/dist/quill.min.js', 'public/vendor/quill/quill.min.js');
mix.copy('node_modules/quill/dist/quill.snow.css', 'public/vendor/quill/quill.snow.css');
mix.copy('node_modules/quill-emoji/dist/quill-emoji.js', 'public/vendor/quill/quill-emoji.js');
mix.copy('node_modules/quill-emoji/dist/quill-emoji.css', 'public/vendor/quill/quill-emoji.css');
mix.copy('node_modules/quill-mention/dist/quill.mention.min.js', 'public/vendor/quill/quill.mention.min.js');
mix.copy('node_modules/quill-mention/dist/quill.mention.min.css', 'public/vendor/quill/quill.mention.min.css');
mix.copy('node_modules/quill-magic-url/dist/index.js', 'public/vendor/quill/quill-magic-url.js');

// Copy Cropper.js assets
mix.copy('node_modules/cropperjs/dist/cropper.min.js', 'public/vendor/cropper/cropper.min.js');
mix.copy('node_modules/cropperjs/dist/cropper.min.css', 'public/vendor/cropper/cropper.min.css');
