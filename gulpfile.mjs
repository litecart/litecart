import gulp from 'gulp'
import cleancss from 'gulp-clean-css'
import concat from 'gulp-concat'
import header from 'gulp-header'
import ignore from 'gulp-ignore'
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

// Build and uglify JS files
gulp.task('js-backend', function() {
  return gulp.src('public_html/backend/template/js/components/*.js')
  .pipe(concat('app.js', {'newLine': '\r\n\r\n'}))
    .pipe(header(banner, { pkg: packageData }))
    .pipe(gulp.dest('public_html/backend/template/js/', { overwrite: true }))
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(rename({ extname: '.min.js' }))
    .pipe(gulp.dest('public_html/backend/template/js/', { overwrite: true }))
    .pipe(sourcemaps.write('.'))
})

gulp.task('js-frontend', function() {
  return gulp.src('public_html/frontend/templates/default/js/components/*.js')
    .pipe(sourcemaps.init())
      .pipe(concat('app.js', {'newLine': '\r\n\r\n'}))
      .pipe(header(banner, { pkg: packageData }))
      .pipe(gulp.dest('public_html/frontend/templates/default/js/', { overwrite: true }))
      .pipe(uglify())
      .pipe(rename({ extname: '.min.js' }))
      .pipe(gulp.dest('public_html/frontend/templates/default/js/', { overwrite: true }))
      .pipe(sourcemaps.write('.'))
})

// Compile LESS files
gulp.task('less-backend', function() {
  return gulp.src([
      'D:/Clients/TiM International/LiteCart/repository (dev-major)/public_html/backend/template/less/app.less',
      'D:/Clients/TiM International/LiteCart/repository (dev-major)/public_html/backend/template/less/framework.less',
      'D:/Clients/TiM International/LiteCart/repository (dev-major)/public_html/backend/template/less/printable.less',
      'D:/Clients/TiM International/LiteCart/repository (dev-major)/public_html/backend/template/less/variables.less'
    ])
    .pipe(less())
    //.pipe(sourcemaps.write('.'))
    .pipe(sourcemaps.init())
    .pipe(header(banner, { pkg: packageData }))
    .pipe(gulp.dest('public_html/backend/template/css/'))
    .pipe(ignore.exclude('**/variables.css'))
    .pipe(cleancss())
    .pipe(rename({ extname: '.min.css' }))
    .pipe(gulp.dest('public_html/backend/template/css/'))
    .pipe(sourcemaps.write('.'))
})

gulp.task('less-frontend', function() {

  return gulp.src([
      'D:/Clients/TiM International/LiteCart/repository (dev-major)/public_html/frontend/templates/default/less/app.less',
      //'D:/Clients/TiM International/LiteCart/repository (dev-major)/public_html/frontend/templates/default/less/email.less',
      'D:/Clients/TiM International/LiteCart/repository (dev-major)/public_html/frontend/templates/default/less/framework.less',
      'D:/Clients/TiM International/LiteCart/repository (dev-major)/public_html/frontend/templates/default/less/printable.less',
      'D:/Clients/TiM International/LiteCart/repository (dev-major)/public_html/frontend/templates/default/less/variables.less'
    ])
    .pipe(sourcemaps.init())
      .pipe(less())
      .pipe(gulp.dest('D:/Clients/TiM International/LiteCart/repository (dev-major)/public_html/frontend/templates/default/css/'))
      .pipe(ignore.exclude('**/variables.css'))
      .pipe(cleancss())
      .pipe(header(banner, { pkg: packageData }))
      .pipe(rename({ extname: '.min.css' }))
      .pipe(sourcemaps.write('.'))
      .pipe(gulp.dest('D:/Clients/TiM International/LiteCart/repository (dev-major)/public_html/frontend/templates/default/css/'))
})

// Task to compile and minify Chartist SCSS
gulp.task('sass-chartist', function() {
  return gulp.src('public_html/assets/chartist/chartist.scss', { allowEmpty: true })
    .pipe(sass().on('error', sass.logError))
    .pipe(sourcemaps.write('.'))
    //.pipe(gulp.dest('public_html/assets/trumbowyg/ui/'))
    .pipe(cleancss())
    .pipe(rename({ extname: '.min.css' }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('public_html/assets/chartist/'))
})

// Task to compile and minify Trumbowyg SCSS
gulp.task('sass-trumbowyg', function() {
  return gulp.src('public_html/assets/trumbowyg/ui/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(sourcemaps.write('.'))
    //.pipe(gulp.dest('public_html/assets/trumbowyg/ui/'))
    .pipe(cleancss())
    .pipe(rename({ extname: '.min.css' }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('public_html/assets/trumbowyg/ui/'))
})

// Lint PHP files
gulp.task('phplint', function() {
  return gulp.src(paths.php)
    .pipe(phplint())
    .pipe(phplint.reporter('fail'))
})

// Watch files for changes
gulp.task('watch', function() {
  gulp.watch('public_html/backend/template/js/components/*.js', gulp.series('js-backend'))
  gulp.watch('public_html/frontend/templates/default/js/components/*.js', gulp.series('js-frontend'))
  gulp.watch('public_html/backend/template/less/**/*.less', gulp.series('less-backend'))
  gulp.watch('public_html/frontend/templates/default/less/**/*.less', gulp.series('less-frontend'))
  gulp.watch('public_html/assets/chartist/chartist.scss', gulp.series('sass-chartist'))
  gulp.watch('public_html/assets/trumbowyg/**/*.scss', gulp.series('sass-trumbowyg'))
})

// Task aliases
gulp.task('build', gulp.series('js-backend', 'js-frontend', 'less-backend', 'less-frontend', 'sass-chartist', 'sass-trumbowyg'))
gulp.task('default', gulp.series('build', 'watch'))