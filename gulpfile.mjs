import gulp from 'gulp'
import cleancss from '@sequencemedia/gulp-clean-css'
import concat from 'gulp-concat'
import download from 'gulp-fetch'
import header from 'gulp-header'
import less from 'gulp-less'
import phplint from 'gulp-phplint'
import rename from 'gulp-rename'
import replace from 'gulp-replace'
import * as dartSass from 'sass'
import gulpSass from 'gulp-sass'
import sourcemaps from '@sequencemedia/gulp-sourcemaps'
import uglify from 'gulp-uglify'

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

gulp.task('less-framework', function() {

  return gulp.src(['public_html/assets/litecore/less/*.less'])
    .pipe(sourcemaps.init())
    .pipe(less())
    .on('error', function (err) {
      console.error('LESS Error:', err.message);
      this.emit('end'); // Prevents Gulp from stopping
    })
    .pipe(gulp.dest('public_html/assets/litecore/css/', { overwrite: true }))
    .pipe(cleancss())
    .pipe(header(banner, { pkg: packageData }))
    .pipe(rename({ extname: '.min.css' }))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/assets/litecore/css/', { overwrite: true }))
})

// Build and uglify JS files
gulp.task('js-framework', function() {
  return gulp
    .src('public_html/assets/litecore/js/components/*.js')
    .pipe(concat('framework.js', {'newLine': '\r\n\r\n'}))
    .pipe(header(banner, { pkg: packageData }))
    .pipe(gulp.dest('public_html/assets/litecore/js/', { overwrite: true }))
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(rename({ extname: '.min.js' }))
    //.pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/assets/litecore/js/', { overwrite: true }))
})

// Compile LESS files
gulp.task('less-backend', function() {

  gulp.src('public_html/backend/template/less/vari*bles.less') // non-globstar pattern will fail on some windows paths
    .pipe(less())
    .on('error', function (err) {
      console.error('LESS Error:', err.message);
      this.emit('end'); // Prevents Gulp from stopping
    })
    .pipe(header(banner, { pkg: packageData }))
    .pipe(gulp.dest('public_html/backend/template/css/', { overwrite: true }))

  return gulp.src(['public_html/backend/template/less/*.less', '!public_html/backend/template/less/vari*bles.less'])
    .pipe(sourcemaps.init())
    .pipe(less())
    .on('error', function (err) {
      console.error('LESS Error:', err.message);
      this.emit('end'); // Prevents Gulp from stopping
    })
    .pipe(header(banner, { pkg: packageData }))
    .pipe(cleancss())
    .pipe(rename({ extname: '.min.css' }))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/backend/template/css', { overwrite: true }))
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
    //.pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/backend/template/js/', { overwrite: true }))
})

gulp.task('less-frontend', function() {

  gulp.src('public_html/frontend/templates/default/less/vari*bles.less') // non-globstar pattern will fail on some windows paths
    .pipe(less())
    .on('error', function (err) {
      console.error('LESS Error:', err.message);
      this.emit('end'); // Prevents Gulp from stopping
    })
    .pipe(header(banner, { pkg: packageData }))
    .pipe(gulp.dest('public_html/frontend/templates/default/css/', { overwrite: true }))

  return gulp.src(['public_html/frontend/templates/default/less/*.less', '!public_html/frontend/templates/default/less/variables*.less'])
    .pipe(sourcemaps.init())
    .pipe(less())
    .on('error', function (err) {
      console.error('LESS Error:', err.message);
      this.emit('end'); // Prevents Gulp from stopping
    })
    .pipe(gulp.dest('public_html/frontend/templates/default/css/', { overwrite: true }))
    .pipe(cleancss())
    .pipe(header(banner, { pkg: packageData }))
    .pipe(rename({ extname: '.min.css' }))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/frontend/templates/default/css/', { overwrite: true }))
})

gulp.task('js-frontend', function() {
  return gulp.src('public_html/frontend/templates/default/js/components/*.js')
    .pipe(concat('app.js', {'newLine': '\r\n\r\n'}))
    .pipe(header(banner, { pkg: packageData }))
    .pipe(gulp.dest('public_html/frontend/templates/default/js/', { overwrite: true }))
    //.pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(rename({ extname: '.min.js' }))
    //.pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/frontend/templates/default/js/', { overwrite: true }))
})

// Task to compile and minify Chartist SCSS
gulp.task('sass-chartist', function() {
  return gulp.src('public_html/assets/chartist/chartist.scss', { allowEmpty: true })
    .pipe(sass().on('error', sass.logError))
    //.pipe(gulp.dest('public_html/assets/chartist/', { overwrite: true }))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(cleancss())
    .pipe(rename({ extname: '.min.css' }))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(gulp.dest('public_html/assets/chartist/', { overwrite: true }))
})

// Task to compile and minify Trumbowyg SCSS
gulp.task('sass-trumbowyg', function() {
  return gulp
    .src('public_html/assets/trumbowyg/ui/*.scss')
	.pipe(sass({ silenceDeprecations: ['legacy-js-api'] })
    .on('error', sass.logError))
    //.pipe(gulp.dest('public_html/assets/trumbowyg/ui/'))
    .pipe(sourcemaps.write('.', { includeContent: false }))
    .pipe(cleancss())
    .pipe(rename({ extname: '.min.css' }))
    .pipe(gulp.dest('public_html/assets/trumbowyg/ui/'))
    .pipe(sourcemaps.write('.', { includeContent: false }))
})

// Lint PHP files
gulp.task('phplint', function() {
  return gulp
    .src(paths.php)
    .pipe(phplint())
    .pipe(phplint.reporter('fail'))
})

gulp.task('iconly', function() {

  download({ url: 'https://dev.iconly.io/public/OoTc8FJRmnEY/iconly.woff2', filename: 'fonticons.woff2' })
    .pipe(gulp.dest('public_html/assets/litecore/fonts/'))

  return download({ url: 'https://dev.iconly.io/public/OoTc8FJRmnEY/iconly.css', filename: 'fonticons.less' })
    .pipe(replace(/^\/\*\!.*?(?=\n.icon-)/gs, [
      '',
      '@font-face {',
      '  font-display: auto;',
      '  font-family: "LiteCore";',
      '  font-style: normal;',
      '  font-weight: 400;',
      `  src: url("../fonts/fonticons.woff2?${Math.floor(Date.now() / 1000)}") format("woff2");`,
      '}',
      '',
      '[class^="icon-"], [class*=" icon-"] {',
      '  display: inline-block;',
      '  font-family: "LiteCore" !important;',
      '  font-weight: 400;',
      '  font-style: normal;',
      '  font-variant: normal;',
      '  text-rendering: auto;',
      '  text-align: center;',
      '  line-height: 1;',
      '  width: 1em;',
      '  height: 1em;',
      '  -moz-osx-font-smoothing: grayscale;',
      '  -webkit-font-smoothing: antialiased;',
      '}',
      '',
    ].join('\n')))
    .pipe(replace(/(\.icon-[^:]+:before)\s*\{\s*([^}]+?)\s*\}\s*/g, '$1 { $2 }\n'))
    .pipe(gulp.dest('public_html/assets/litecore/less/framework/'))
})

// Watch files for changes
gulp.task('watch', function() {
  gulp.watch('public_html/assets/chartist/chartist.scss', gulp.series('sass-chartist'))
  gulp.watch('public_html/assets/litecore/less/**/*.less', gulp.series('less-framework'))
  gulp.watch('public_html/assets/litecore/js/components/*.js', gulp.series('js-framework'))
  gulp.watch('public_html/assets/trumbowyg/**/*.scss', gulp.series('sass-trumbowyg'))
  gulp.watch('public_html/backend/template/less/**/*.less', gulp.series('less-backend'))
  gulp.watch('public_html/backend/template/js/components/*.js', gulp.series('js-backend'))
  gulp.watch('public_html/frontend/templates/default/less/**/*.less', gulp.series('less-frontend'))
  gulp.watch('public_html/frontend/templates/default/js/components/*.js', gulp.series('js-frontend'))
})

// Task aliases
gulp.task('build', gulp.series(
  'iconly',
  'js-framework',
  'js-backend',
  'js-frontend',
  'less-framework',
  'less-backend',
  'less-frontend',
  'sass-chartist',
  'sass-trumbowyg',
  'watch',
))

gulp.task('default', gulp.series('build'))
