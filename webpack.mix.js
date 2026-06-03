const mix = require('laravel-mix');
const fs = require("fs-extra");
const path = require("path");
const cliColor = require("cli-color");
const emojic = require("emojic");
const wpPot = require('wp-pot');
const archiver = require("archiver");
const min = mix.inProduction() ? '.min' : '';

const package_path = path.resolve(__dirname);
const package_slug = path.basename(path.resolve(package_path));
const temDirectory = package_path + "/temp";

mix.options({
    terser: {
        extractComments: false,
    },
    processCssUrls: false
});

if (process.env.npm_config_package) {

    mix.then(function () {
        const copyTo = path.resolve(`${temDirectory}/${package_slug}`);
        // Select All file then paste on list
        let includes = [
            'app',
            'assets',
            'languages',
            'src',
            'templates',
            'vendor',
            'composer.json',
            'index.php',
            `${package_slug}.php`];
        fs.ensureDir(copyTo, function (err) {
            if (err) return console.error(err);
            includes.map(include => {
                fs.copy(`${package_path}/${include}`, `${copyTo}/${include}`, function (err) {
                    if (err) return console.error(err);
                    console.log(cliColor.white(`=> ${emojic.smiley}  ${include} copied...`));
                })
            });
            console.log(cliColor.white(`=> ${emojic.whiteCheckMark}  Build directory created`));
        });
    });

    return;
}

if ((!process.env.npm_config_block && !process.env.npm_config_package) && (process.env.NODE_ENV === 'development' || process.env.NODE_ENV === 'production')) {

    if (mix.inProduction()) {
        let languages = path.resolve('languages');
        fs.ensureDir(languages, function (err) {
            if (err) return console.error(err); // if a file or folder does not exist
            wpPot({
                package: 'Classified Listing - Offload Media',
                bugReport: '',
                src: [
                    'app/**/*.php',
                    'rtcl-offload-media.php'
                ],
                domain: 'rtcl-offload-media',
                destFile: `languages/rtcl-offload-media.pot`
            });
        });

    }

    mix
        .babel(`src/js/admin.js`, `assets/js/admin${min}.js`)

}