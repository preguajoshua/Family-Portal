const mix = require( "laravel-mix" );
const { exec } = require( "child_process" );

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// Styles
mix
    .sass( "resources/sass/app.scss", "public/css", { sourceComments: true })
    .sass( "resources/sass/site.scss", "public/css", { sourceComments: true })
    .styles([
        "node_modules/animate.css/animate.css",
        "node_modules/bootstrapvalidator/dist/css/bootstrapValidator.css",
        "node_modules/fullcalendar/dist/fullcalendar.css",
        "node_modules/pickadate/lib/themes/classic.css",
        "node_modules/pickadate/lib/themes/classic.date.css",
        "node_modules/pickadate/lib/themes/classic.time.css",
        "node_modules/toastr/build/toastr.css",
        "node_modules/bootstrap/dist/css/bootstrap.css",
    ], "public/css/vendor.css" )
    .copy( "resources/css/braintree-hosted-fields.css", "public/css/braintree-hosted-fields.css" )
    .copy( "resources/css/font-awesome.css", "public/css/font-awesome.css" );

// Scripts
mix
    .copy( "node_modules/can/dist/amd", "public/js/libs/can" )
    .copy( "node_modules/bootbox/bootbox.js", "public/js/libs/bootbox.js" )
    .copy( "node_modules/bootstrap/dist/js/bootstrap.js", "public/js/libs/bootstrap.js" )
    .copy( "node_modules/bootstrapvalidator/dist/js/bootstrapValidator.min.js", "public/js/libs/bootstrapValidator.js" )
    .copy( "node_modules/fullcalendar/dist/fullcalendar.js", "public/js/libs/fullcalendar.js" )
    .copy( "node_modules/jquery/dist/jquery.js", "public/js/libs/jquery.js" )
    .copy( "node_modules/jquery-mask-plugin/dist/jquery.mask.js", "public/js/libs/jquery.mask.js" )
    .copy( "node_modules/jQuery.print/jQuery.print.js", "public/js/libs/jquery.print.js" )
    .copy( "node_modules/jquery-sticky/jquery.sticky.js", "public/js/libs/sticky.js" )
    .copy( "node_modules/moment/moment.js", "public/js/libs/moment.js" )
    .copy( "node_modules/pace-progressbar/pace.js", "public/js/libs/pace.js" )
    .copy( "node_modules/pickadate/lib/picker.date.js", "public/js/libs/picker.date.js" )
    .copy( "node_modules/pickadate/lib/picker.js", "public/js/libs/picker.js" )
    .copy( "node_modules/pickadate/lib/picker.time.js", "public/js/libs/picker.time.js" )
    .copy( "node_modules/requirejs/require.js", "public/js/libs/require.js" )
    .copy( "node_modules/toastr/toastr.js", "public/js/libs/toastr.js" )
    .copy( "resources/js/client.min.js", "public/js/libs/braintree/client.min.js" )
    .copy( "resources/js/hosted-fields.min.js", "public/js/libs/braintree/hosted-fields.min.js" )
    .then( () => {
        let buildFile = "public/js/build.js";
        let outFile = "public/js/dist/app.js";

        exec( `./node_modules/requirejs/bin/r.js -o ${buildFile} out=${outFile}`, ( error, stdout, stderr ) => {
            if (error) {
                console.error( `exec error: ${error}` );
                return;
            }

            console.log( stdout );
        });
    });


// Versioning
mix
    .version([
        "public/js/dist/app.js",
    ]);
