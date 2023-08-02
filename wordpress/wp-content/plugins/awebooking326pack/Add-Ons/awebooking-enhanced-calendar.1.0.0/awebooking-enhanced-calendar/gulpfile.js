'use strict';

const gulp         = require('gulp');
const plumber      = require('gulp-plumber');
const notify       = require('gulp-notify');
const sourcemaps   = require('gulp-sourcemaps');
const sass         = require('gulp-sass');
const autoprefixer = require('gulp-autoprefixer');
const gcmq         = require('gulp-group-css-media-queries');
const cleanCSS     = require('gulp-clean-css');
const rollup       = require('gulp-better-rollup');
const babel        = require('rollup-plugin-babel');
const commonjs     = require('rollup-plugin-commonjs');
const resolve      = require('rollup-plugin-node-resolve');
const uglify       = require('gulp-uglify');
const rename       = require('gulp-rename');
const potgen       = require('gulp-wp-pot');
const browserSync  = require('browser-sync').create();
const del          = require('del');
const pkg          = require('./package.json');

/**
 * Handle errors and alert the user.
 */
function handleErrors() {
  const args = Array.prototype.slice.call(arguments);

  notify.onError({
    title: 'Task Failed! See console.',
    message: '<%= error.message %>',
  }).apply(this, args);

  // Prevent the 'watch' task from stopping
  this.emit('end');
}

gulp.task('scss', () => {
  return gulp.src('assets/scss/*.scss')
    .pipe(plumber({ errorHandler: handleErrors }))
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(gcmq())
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('assets/css'))
    .pipe(browserSync.stream({ match: '**/*.css' }));
});

gulp.task('babel', () => {
  const config = {
    external: Object.keys(pkg.globals),
    plugins: [
      resolve(),
      commonjs(),
      babel()
    ]
  };

  return gulp.src('assets/babel/*.js')
    .pipe(plumber({ errorHandler: handleErrors }))
    .pipe(sourcemaps.init())
    .pipe(rollup(config, {
      format: 'iife',
      globals: pkg.globals
    }))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('assets/js'));
});

gulp.task('minify:js', () => {
  return gulp.src(['assets/js/**/*.js', '!assets/js/**/*.min.js'])
    .pipe(plumber({ errorHandler: handleErrors }))
    .pipe(uglify())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('assets/js'));
});

gulp.task('minify:css', () => {
  return gulp.src(['assets/css/*.css', '!assets/css/*.min.css'])
    .pipe(plumber({ errorHandler: handleErrors }))
    .pipe(cleanCSS())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('assets/css'));
});

gulp.task('i18n', () => {
  return gulp.src(['*.php', 'inc/**/*.php', 'templates/**/*.php', '!vendor/**', '!tests/**'])
    .pipe(plumber())
    .pipe(potgen({ domain: pkg.name, package: 'AweBooking Fees' }))
    .pipe(gulp.dest(`languages/${pkg.name}.pot`));
});

gulp.task('clean', () => {
  return del([
    'assets/js/**/*.{js,map}',
    'assets/css/**/*.{css,map}',
  ]);
});

gulp.task('watch', () => {
  browserSync.init({
    proxy: 'awebooking.local',
  });

  gulp.watch('assets/scss/**/*.scss', gulp.series('scss'));
  gulp.watch('assets/babel/**/*.js', gulp.series('babel'));
});

gulp.task('js', gulp.series(['babel', 'minify:js']));
gulp.task('css', gulp.series(['scss', 'minify:css']));
gulp.task('default', gulp.series(['clean', 'css', 'js', 'i18n']));
