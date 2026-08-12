import gulp from 'gulp';
import cleancss from '@sequencemedia/gulp-clean-css';
import concat from 'gulp-concat';
import download from 'gulp-fetch';
import header from 'gulp-header';
import rename from 'gulp-rename';
import replace from 'gulp-replace';
import * as dartSass from 'sass';
import gulpSass from 'gulp-sass';
import sourcemaps from '@sequencemedia/gulp-sourcemaps';
import uglify from 'gulp-uglify';
import { Transform } from 'stream';

import packageData from './package.json' with { type: 'json' };

const sass = gulpSass(dartSass);
const sassOptions = { charset: false, silenceDeprecations: ['legacy-js-api'] };

const banner = [
	'/*!',
	'	<%= pkg.title %> v<%= pkg.version %> - <%= pkg.description %>',
	'	Link: <%= pkg.homepage %>',
	'	License: <%= pkg.license %>',
	'	Author: <%= pkg.author.name %>',
	'*/',
	'',
	'',
].join('\n');

const tabify = function() {
	const indentSize = 2;
	return new Transform({
		objectMode: true,
		transform(file, _, cb) {
			if (file.isBuffer()) {
				let contents = file.contents.toString();
				const newline = contents.indexOf('\r\n') !== -1 ? '\r\n' : '\n';
				const lines = contents.split(/\r\n|\n/);
				for (let i = 0; i < lines.length; i++) {
					const line = lines[i];
					const m = line.match(/^([ \t]*)/);
					if (!m) continue;
					const ws = m[1];
					if (!ws) continue;
					let spaceCount = 0;
					for (const ch of ws) {
						if (ch === '\t') spaceCount += indentSize;
						else spaceCount += 1;
					}
					const tabs = Math.floor(spaceCount / indentSize);
					let leftover = spaceCount % indentSize;
					const rest = line.slice(ws.length);
					// For comment lines that start with '*', ensure there is at least one space
					// after the tabs so the asterisk remains separated ("\t * ...").
					if (rest.startsWith('*') && leftover === 0) leftover = 1;
					lines[i] = '\t'.repeat(tabs) + (leftover ? ' '.repeat(leftover) : '') + rest;
				}
				contents = lines.join(newline);
				file.contents = Buffer.from(contents);
			}
			cb(null, file);
		}
	});
}

gulp.task('scss-framework', function() {
	return gulp.src('src/assets/litecore/scss/{framework/main,email,printable}.scss', { allowEmpty: true })
		.pipe(sourcemaps.init())
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(rename(function(path) {
			if (path.dirname == 'framework' && path.basename == 'main') {
				path.dirname = '';
				path.basename = 'framework';
			}
		}))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(tabify()) // Use tab indentation
		.pipe(gulp.dest('src/assets/litecore/css/', { overwrite: true }))
		.pipe(cleancss())
		.pipe(rename({ extname: '.min.css' }))
		.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('src/assets/litecore/css/', { overwrite: true }));
});

// Build and uglify JS files
gulp.task('js-framework', function() {
	return gulp
		.src('src/assets/litecore/js/framework/*.js')
		.pipe(concat('framework.js', {'newLine': '\r\n\r\n'}))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(gulp.dest('src/assets/litecore/js/', { overwrite: true }))
		.pipe(sourcemaps.init())
		.pipe(uglify())
		.pipe(rename({ extname: '.min.js' }))
		.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('src/assets/litecore/js/', { overwrite: true }));
});

gulp.task('scss-backend', function() {
	gulp.src('src/backend/template/scss/vari*bles.scss')
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(tabify())
		.pipe(gulp.dest('src/backend/template/css/', { overwrite: true }));

	return gulp.src(['src/backend/template/scss/*.scss', '!src/backend/template/scss/variables.scss'])
		.pipe(sourcemaps.init())
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(tabify()) // Use tab indentation
		//.pipe(gulp.dest('src/backend/template/css/', { overwrite: true }))
		.pipe(cleancss())
		.pipe(rename({ extname: '.min.css' }))
		.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('src/backend/template/css', { overwrite: true }));
});

// Build and uglify JS files
gulp.task('js-backend', function() {
	return gulp
		.src('src/backend/template/js/components/*.js')
		.pipe(concat('app.js', {'newLine': '\r\n\r\n'}))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(tabify()) // Use tab indentation
		.pipe(gulp.dest('src/backend/template/js/', { overwrite: true }))
		.pipe(sourcemaps.init())
		.pipe(uglify())
		.pipe(rename({ extname: '.min.js' }))
		//.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('src/backend/template/js/', { overwrite: true }));
});

// Build and uglify JS files
gulp.task('js-trumbowyg', function() {
	return gulp
		.src('src/assets/trumbowyg/trumb*wyg.js')
		.pipe(sourcemaps.init())
		.pipe(uglify())
		.pipe(rename({ extname: '.min.js' }))
		//.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('src/assets/trumbowyg/', { overwrite: true }));
});

gulp.task('scss-frontend', function() {
	gulp.src('src/frontend/templates/default/scss/vari*bles.scss')
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(tabify()) // Use tab indentation
		.pipe(gulp.dest('src/frontend/templates/default/css/', { overwrite: true }));

	return gulp.src(['src/frontend/templates/default/scss/*.scss', '!src/frontend/templates/default/scss/variables*.scss'])
		.pipe(sourcemaps.init())
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(tabify()) // Use tab indentation
		.pipe(gulp.dest('src/frontend/templates/default/css/', { overwrite: true }))
		.pipe(cleancss())
		.pipe(header(banner, { pkg: packageData }))
		.pipe(rename({ extname: '.min.css' }))
		.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('src/frontend/templates/default/css/', { overwrite: true }));
});

gulp.task('js-frontend', function() {
	return gulp.src('src/frontend/templates/default/js/components/*.js')
		.pipe(concat('app.js', {'newLine': '\r\n\r\n'}))
		.pipe(header(banner, { pkg: packageData }))
		.pipe(gulp.dest('src/frontend/templates/default/js/', { overwrite: true }))
		//.pipe(sourcemaps.init())
		.pipe(uglify())
		.pipe(rename({ extname: '.min.js' }))
		//.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('src/frontend/templates/default/js/', { overwrite: true }));
});

// Task to compile and minify Chartist SCSS
gulp.task('scss-chartist', function() {
	return gulp.src('src/assets/chartist/chartist.scss', { allowEmpty: true })
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(tabify()) // Use tab indentation
		//.pipe(gulp.dest('src/assets/chartist/', { overwrite: true }))
		//.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(cleancss())
		.pipe(rename({ extname: '.min.css' }))
		.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(gulp.dest('src/assets/chartist/', { overwrite: true }));
});

// Task to compile and minify Trumbowyg SCSS
gulp.task('scss-trumbowyg', function() {
	return gulp
		.src('src/assets/trumbowyg/ui/*.scss')
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(tabify()) // Use tab indentation
		//.pipe(gulp.dest('src/assets/trumbowyg/ui/'))
		//.pipe(sourcemaps.write('.', { includeContent: false }))
		.pipe(cleancss())
		.pipe(rename({ extname: '.min.css' }))
		.pipe(gulp.dest('src/assets/trumbowyg/ui/'))
		.pipe(sourcemaps.write('.', { includeContent: false }));
});

gulp.task('iconly', function() {
	download({ url: 'https://dev.iconly.io/public/OoTc8FJRmnEY/iconly.woff2', filename: 'fonticons.woff2' })
		.pipe(gulp.dest('src/assets/litecore/fonts/'));

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
		.pipe(tabify()) // Use tab indentation
		.pipe(gulp.dest('src/assets/litecore/scss/framework/'));
});

// Watch files for changes
gulp.task('watch', function() {
	gulp.watch('src/assets/chartist/chartist.scss', gulp.series('scss-chartist'))
	gulp.watch('src/assets/litecore/scss/**/*.scss', gulp.series('scss-framework'))
	gulp.watch('src/assets/litecore/ts/framework/*.ts', gulp.series('js-framework'))
	gulp.watch('src/assets/trumbowyg/trumbowyg.js', gulp.series('js-trumbowyg'))
	gulp.watch('src/assets/trumbowyg/**/*.scss', gulp.series('scss-trumbowyg'))
	gulp.watch('src/backend/template/scss/**/*.scss', gulp.series('scss-backend'))
	gulp.watch('src/backend/template/js/components/*.js', gulp.series('js-backend'))
	gulp.watch('src/frontend/templates/default/scss/**/*.scss', gulp.series('scss-frontend'))
	gulp.watch('src/frontend/templates/default/js/components/*.js', gulp.series('js-frontend'))
});

// Task aliases
const buildTasks = [
	'js-framework',
	'js-backend',
	'js-frontend',
	'js-trumbowyg',
	'scss-framework',
	'scss-backend',
	'scss-frontend',
	'scss-chartist',
	'scss-trumbowyg',
];

gulp.task('build', gulp.series(...buildTasks, 'watch'));
gulp.task('build:once', gulp.series(...buildTasks));

gulp.task('default', gulp.series(
	'build',
));
