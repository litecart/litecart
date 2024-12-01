import gulp from 'gulp'
import cleancss from 'gulp-clean-css'
import concat from 'gulp-concat'
import header from 'gulp-header'
import less from 'gulp-less'
import phplint from 'gulp-phplint'
import rename from 'gulp-rename'
import dartSass from 'sass'
import gulpSass from 'gulp-sass'
import sourcemaps from '@sequencemedia/gulp-sourcemaps'
import uglify from 'gulp-uglify'
import watch from 'gulp-watch'

import packageData from './package.json' with { type: 'json' }

const sass = gulpSass(dartSass)

const banner = [
  '/*!',
  ' * <%= pkg.title %> v<%= pkg.version %> - <%= pkg.description %>',
  ' * @link <%= pkg.homepage %>',
  ' * @license <%= pkg.license %>',
  ' * @author <%= pkg.author.name %>',
  ' */',
  '',
  '',
].join('\n')

// Compile LESS files
gulp.task('less-backend', function() {

  gulp
    .src('public_html/backend/template/less/vari*bles.less') // non-globstar pattern will fail on some windows paths
    .pipe(less())
    .pipe(header(banner, { pkg: packageData }))
    .pipe(gulp.dest('public_html/backend/template/css/', { overwrite: true }))

  return gulp
    .src(['public_html/backend/template/less/*.less', '!public_html/backend/template/less/variables*.less'])
    .pipe(sourcemaps.init())
    .pipe(less())
    .pipe(header(banner, { pkg: packageData }))
    .pipe(cleancss())
    .pipe(rename({ extname: '.min.css' }))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/backend/template/css', { overwrite: true }))
})

gulp.task('less-frontend', function() {

  gulp
    .src('public_html/frontend/templates/default/less/vari*bles.less') // non-globstar pattern will fail on some windows paths
    .pipe(less())
    .pipe(header(banner, { pkg: packageData }))
    .pipe(gulp.dest('public_html/frontend/templates/default/css/', { overwrite: true }))

  return gulp
    .src(['public_html/frontend/templates/default/less/*.less', '!public_html/frontend/templates/default/less/variables*.less'])
    .pipe(sourcemaps.init())
    .pipe(less())
    .pipe(gulp.dest('public_html/frontend/templates/default/css/', { overwrite: true }))
    .pipe(cleancss())
    .pipe(header(banner, { pkg: packageData }))
    .pipe(rename({ extname: '.min.css' }))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/frontend/templates/default/css/', { overwrite: true }))
})

// Build and uglify JS files
gulp.task('js-backend', function() {
  return gulp
    .src('public_html/backend/template/js/components/*.js')
    .pipe(concat('app.js', {'newLine': '\r\n\r\n'}))
    .pipe(header(banner, { pkg: packageData }))
    .pipe(gulp.dest('public_html/backend/template/js/', { overwrite: true }))
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(rename({ extname: '.min.js' }))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/backend/template/js/', { overwrite: true }))
})

gulp.task('js-frontend', function() {
  return gulp
    .src('public_html/frontend/templates/default/js/components/*.js')
    .pipe(sourcemaps.init())
    .pipe(concat('app.js', {'newLine': '\r\n\r\n'}))
    .pipe(header(banner, { pkg: packageData }))
    .pipe(gulp.dest('public_html/frontend/templates/default/js/', { overwrite: true }))
    .pipe(uglify())
    .pipe(rename({ extname: '.min.js' }))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/frontend/templates/default/js/', { overwrite: true }))
})

// Task to compile and minify Chartist SCSS
gulp.task('sass-chartist', function() {
  return gulp
    .src('public_html/assets/chartist/chartist.scss', { allowEmpty: true })
    .pipe(sass().on('error', sass.logError))
    //.pipe(gulp.dest('public_html/assets/trumbowyg/ui/', { overwrite: true }))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(cleancss())
    .pipe(rename({ extname: '.min.css' }))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/assets/chartist/', { overwrite: true }))
})

// Task to compile and minify Featherlight
gulp.task('featherlight', function() {

  gulp
    .src(['public_html/assets/featherlight/featherli*ht.less'])
    .pipe(less())
    .pipe(cleancss())
    .pipe(rename({ extname: '.min.css' }))
    .pipe(gulp.dest('public_html/assets/featherlight/', { overwrite: true }))

    return gulp
    .src(['public_html/assets/featherlight/featherli*ht.js'])
    .pipe(uglify())
    .pipe(rename({ extname: '.min.js' }))
    .pipe(gulp.dest('public_html/assets/featherlight/', { overwrite: true }))
})

// Task to compile and minify Trumbowyg SCSS
gulp.task('sass-trumbowyg', function() {
  return gulp
    .src('public_html/assets/trumbowyg/ui/*.scss')
    .pipe(sass().on('error', sass.logError))
    //.pipe(gulp.dest('public_html/assets/trumbowyg/ui/'))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(cleancss())
    .pipe(rename({ extname: '.min.css' }))
    .pipe(gulp.dest('public_html/assets/trumbowyg/ui/'))
    .pipe(sourcemaps.write('.', { includeContent: false }))
})

// Lint PHP files
gulp.task('phplint', () => {
  return gulp
    .src(paths.php)
    .pipe(phplint())
    .pipe(phplint.reporter('fail'))
})

// Watch files for changes
gulp.task('watch', () => {
  gulp.watch('public_html/assets/chartist/chartist.scss', gulp.series('sass-chartist'))
  gulp.watch('public_html/assets/trumbowyg/**/*.scss', gulp.series('sass-trumbowyg'))
  gulp.watch('public_html/backend/template/less/**/*.less', gulp.series('less-backend'))
  gulp.watch('public_html/backend/template/js/components/*.js', gulp.series('js-backend'))
  gulp.watch('public_html/frontend/templates/default/less/**/*.less', gulp.series('less-frontend'))
  gulp.watch('public_html/frontend/templates/default/js/components/*.js', gulp.series('js-frontend'))
})

// Task aliases
gulp.task('build', gulp.series('js-backend', 'js-frontend', 'less-backend', 'less-frontend', 'featherlight', 'sass-chartist', 'sass-trumbowyg'))
gulp.task('default', gulp.series('build', 'watch'))