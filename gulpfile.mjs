import gulp from 'gulp';
import cleancss from '@sequencemedia/gulp-clean-css';
import concat from 'gulp-concat';
import download from 'gulp-fetch';
import header from 'gulp-header';
import phplint from 'gulp-phplint';
import rename from 'gulp-rename';
import replace from 'gulp-replace';
import * as dartSass from 'sass';
import gulpSass from 'gulp-sass';
import sourcemaps from '@sequencemedia/gulp-sourcemaps';
import uglify from 'gulp-uglify';

import packageData from './package.json' with { type: 'json' };

const sass = gulpSass(dartSass);
const sassOptions = { charset: false };

const banner = [
	'/*!',
	' * <%= pkg.title %> v<%= pkg.version %> - <%= pkg.description %>',
	' * @link <%= pkg.homepage %>',
	' * @license <%= pkg.license %>',
	' * @author <%= pkg.author.name %>',
	' */',
	'',
	'',
].join('\n');

gulp.task('scss-framework', function() {

	return gulp.src('public_html/assets/litecore/scss/{framework/main,email,printable}.scss', { allowEmpty: true })
		.pipe(sourcemaps.init())
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(rename(function(path) {
			if (path.dirname == 'framework' && path.basename == 'main') {
				path.dirname = '';
				path.basename = 'framework';
			}
		}))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(gulp.dest('public_html/assets/litecore/css/', { overwrite: true }))
		.pipe(cleancss())
		.pipe(rename({ extname: '.min.css' }))
		.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('public_html/assets/litecore/css/', { overwrite: true }));
});

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
		.pipe(gulp.dest('public_html/assets/litecore/js/', { overwrite: true }));
});

gulp.task('scss-backend', function() {

	gulp.src('public_html/backend/template/scss/vari*bles.scss')
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(gulp.dest('public_html/backend/template/css/', { overwrite: true }));

	return gulp.src(['public_html/backend/template/scss/*.scss', '!public_html/backend/template/scss/variables.scss'])
		.pipe(sourcemaps.init())
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(cleancss())
		.pipe(rename({ extname: '.min.css' }))
		.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('public_html/backend/template/css', { overwrite: true }));
});

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
		.pipe(gulp.dest('public_html/backend/template/js/', { overwrite: true }));
});

// Build and uglify JS files
gulp.task('js-trumbowyg', function() {
	return gulp
		.src('public_html/assets/trumbowyg/trumb*wyg.js')
		.pipe(sourcemaps.init())
		.pipe(uglify())
		.pipe(rename({ extname: '.min.js' }))
		//.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('public_html/assets/trumbowyg/', { overwrite: true }));
});

gulp.task('scss-frontend', function() {

	gulp.src('public_html/frontend/templates/default/scss/vari*bles.scss')
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(gulp.dest('public_html/frontend/templates/default/css/', { overwrite: true }));

	return gulp.src(['public_html/frontend/templates/default/scss/*.scss', '!public_html/frontend/templates/default/scss/variables*.scss'])
		.pipe(sourcemaps.init())
		.pipe(sass({ ...sassOptions, silenceDeprecations: ['import'] }).on('error', sass.logError))
		.pipe(gulp.dest('public_html/frontend/templates/default/css/', { overwrite: true }))
		.pipe(cleancss())
		.pipe(header(banner, { pkg: packageData }))
		.pipe(rename({ extname: '.min.css' }))
		.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('public_html/frontend/templates/default/css/', { overwrite: true }));
});

gulp.task('js-frontend', function() {
	return gulp.src('public_html/frontend/templates/default/js/components/*.js')
		.pipe(concat('app.js', {'newLine': '\r\n\r\n'}))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(gulp.dest('public_html/frontend/templates/default/js/', { overwrite: true }))
		//.pipe(sourcemaps.init())
		.pipe(uglify())
		.pipe(rename({ extname: '.min.js' }))
		//.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('public_html/frontend/templates/default/js/', { overwrite: true }));
});

// Task to compile and minify Chartist SCSS
gulp.task('scss-chartist', function() {
	return gulp.src('public_html/assets/chartist/chartist.scss', { allowEmpty: true })
		.pipe(sass(sassOptions).on('error', sass.logError))
		//.pipe(gulp.dest('public_html/assets/chartist/', { overwrite: true }))
		//.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(cleancss())
		.pipe(rename({ extname: '.min.css' }))
		.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('public_html/assets/chartist/', { overwrite: true }));
});

// Task to compile and minify Trumbowyg SCSS
gulp.task('scss-trumbowyg', function() {
	return gulp
		.src('public_html/assets/trumbowyg/ui/*.scss')
		.pipe(sass({ ...sassOptions, silenceDeprecations: ['legacy-js-api'] })
		.on('error', sass.logError))
		//.pipe(gulp.dest('public_html/assets/trumbowyg/ui/'))
		//.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(cleancss())
		.pipe(rename({ extname: '.min.css' }))
		.pipe(gulp.dest('public_html/assets/trumbowyg/ui/'))
		.pipe(sourcemaps.write('.', { includeContent: false }));
});

// Lint PHP files
gulp.task('phplint', function() {
	return gulp
		.src('public_html/**/*.php')
		.pipe(phplint())
		.pipe(phplint.reporter('fail'));
});

gulp.task('iconly', function() {

	download({ url: 'https://dev.iconly.io/public/OoTc8FJRmnEY/iconly.woff2', filename: 'fonticons.woff2' })
		.pipe(gulp.dest('public_html/assets/litecore/fonts/'));

	return download({ url: 'https://dev.iconly.io/public/OoTc8FJRmnEY/iconly.css', filename: '_fonticons.scss' })
		.pipe(replace(/^\/\*\!.*?(?=\n.icon-)/gs, [
			'',
			'@font-face {',
			'	font-display: auto;',
			'	font-family: "LiteCore";',
			'	font-style: normal;',
			'	font-weight: 400;',
			`	src: url("../fonts/fonticons.woff2?${Math.floor(Date.now() / 1000)}") format("woff2");`,
			'}',
			'',
			'[class^="icon-"], [class*=" icon-"] {',
			'	display: inline-block;',
			'	font-family: "LiteCore" !important;',
			'	font-weight: 400;',
			'	font-style: normal;',
			'	font-variant: normal;',
			'	text-rendering: auto;',
			'	text-align: center;',
			'	vertical-align: middle;',
			'	line-height: 1;',
			'	width: 1em;',
			'	height: 1em;',
			'	-moz-osx-font-smoothing: grayscale;',
			'	-webkit-font-smoothing: antialiased;',
			'}',
			'',
		].join('\n')))
		.pipe(replace(/(\.icon-[^:]+:before)\s*\{\s*([^}]+?)\s*\}\s*/g, '$1 { $2 }\n'))
		.pipe(gulp.dest('public_html/assets/litecore/scss/framework/'));
});

// Watch files for changes
gulp.task('watch', function() {
	gulp.watch('public_html/assets/chartist/chartist.scss', gulp.series('scss-chartist'))
	gulp.watch('public_html/assets/litecore/scss/**/*.scss', gulp.series('scss-framework'))
	gulp.watch('public_html/assets/litecore/js/components/*.js', gulp.series('js-framework'))
	gulp.watch('public_html/assets/trumbowyg/trumbowyg.js', gulp.series('js-trumbowyg'))
	gulp.watch('public_html/assets/trumbowyg/**/*.scss', gulp.series('scss-trumbowyg'))
	gulp.watch('public_html/backend/template/scss/**/*.scss', gulp.series('scss-backend'))
	gulp.watch('public_html/backend/template/js/components/*.js', gulp.series('js-backend'))
	gulp.watch('public_html/frontend/templates/default/scss/**/*.scss', gulp.series('scss-frontend'))
	gulp.watch('public_html/frontend/templates/default/js/components/*.js', gulp.series('js-frontend'))
});

// Task aliases
gulp.task('build', gulp.series(
	'js-framework',
	'js-backend',
	'js-frontend',
	'js-trumbowyg',
	'scss-framework',
	'scss-backend',
	'scss-frontend',
	'scss-chartist',
	'scss-trumbowyg',
	'watch',
));

gulp.task('default', gulp.series(
	'build',
	'watch'
));
