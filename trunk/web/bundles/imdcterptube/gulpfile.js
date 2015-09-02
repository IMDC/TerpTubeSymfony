var gulp = require('gulp');
var runSequence = require('run-sequence');
var del = require('del');
var path = require('path');
var dust = require('gulp-dust');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var rjs = require('gulp-requirejs');
var compass = require('gulp-compass');
var minifyCss = require('gulp-minify-css');
var rename = require('gulp-rename');
var rev = require('gulp-rev');
var fs = require('fs');
var replace = require('gulp-replace');
var watch = require('gulp-watch');

gulp.task('clean', function () {
    return del.sync(['./build', './js/terptube.min-*', './css/terptube.min-*']);
});

gulp.task('rjs', function () {
    rjs({
        mainConfigFile: '_js/config.js',
        baseUrl: '_js/app',
        insertRequire: ['main'],
        name: 'main',
        out: 'terptube.js'
    })
        .pipe(gulp.dest('./build/js'));
});

gulp.task('dust', function () {
    return gulp.src('./_js/app/template/**/*.dust')
        .pipe(dust({
            name: function (file) {
                return path.basename(file.path, '.dust');
            }
        }))
        .pipe(concat('templates.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('./_js/app/lib'));
});

gulp.task('scripts', ['rjs', 'dust'], function () {
    return gulp.src('./build/js/*.js')
        .pipe(concat('terptube.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('./build/js'));
});

gulp.task('sass', ['fonts'], function () {
    return gulp.src('_css/sass/*.scss')
        .pipe(compass({
            config_file: __dirname + '/_css/config.rb',
            css: 'css',
            sass: __dirname + '/_css/sass'//, debug: true
        }))
        .pipe(gulp.dest('./build/css'))
        .pipe(minifyCss())
        .pipe(rename(function (path) {
            path.basename += '.min'
        }))
        .pipe(gulp.dest('./build/css'));
});

gulp.task('fonts', function () {
    return gulp.src(['_css/fonts/*'])
        .pipe(gulp.dest('./build/fonts'))
        .pipe(gulp.dest('./fonts'));
});

var hashes = {};

gulp.task('revision', function () {
    return gulp.src('./build/**/*.min.{css,js}')
        .pipe(rev())
        .pipe(gulp.dest('.'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('./build'));
});

gulp.task('build', function () {
    return runSequence('clean', ['scripts', 'sass'], 'revision', function () {
        var manifest = JSON.parse(fs.readFileSync('./build/rev-manifest.json', 'utf8'));

        gulp.src('../../../src/IMDC/TerpTubeBundle/Resources/config/version.yml')
            .pipe(replace(/scripts:.*/g, 'scripts: ' + manifest['js/terptube.min.js']))
            .pipe(replace(/sass:.*/g, 'sass: ' + manifest['css/terptube.min.css']))
            .pipe(gulp.dest('../../../src/IMDC/TerpTubeBundle/Resources/config'));
    });
});

gulp.task('watch', ['build'], function () {
    watch(['_js/**/*.js', '!_js/app/bower_components/', '!_js/app/node_modules/'], {read: false}, function(events, done) {
        gulp.start('scripts');
    });
    watch('_css/sass/**/*.scss', {read: false}, function (events, done) {
        gulp.start('sass');
    });
});

gulp.task('default', ['build']);
