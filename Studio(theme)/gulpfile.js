import gulp from 'gulp';
import * as dartSass from 'sass';
import gulpSass from 'gulp-sass';
import minifyCSS from'gulp-clean-css';
import concat from'gulp-concat';
import sourcemaps from'gulp-sourcemaps';
import livereload from'gulp-livereload';
import connect from'gulp-connect';
import uglify from'gulp-uglify';
import rename from'gulp-rename';
import fileinclude from'gulp-file-include';
import autoprefixer from'gulp-autoprefixer';
import copy from 'gulp-copy';

const sass = gulpSass(dartSass);

export function html() {
	return gulp.src(['src/html/*.html'])
		.pipe(fileinclude({
			prefix: '@@',
			basepath: '@file',
			rootPath: './'
		}))
		.pipe(gulp.dest('dist/'))
		.pipe(livereload());
};

export function cssVendor() {
  return gulp.src([
		'node_modules/@fortawesome/fontawesome-free/css/all.min.css',
		'node_modules/jquery-ui-dist/jquery-ui.min.css',
		'node_modules/pace-js/themes/black/pace-theme-flash.css',
		'node_modules/perfect-scrollbar/css/perfect-scrollbar.css'
	])
	.pipe(sass())
	.pipe(concat('vendor.min.css'))
	.pipe(minifyCSS())
	.pipe(gulp.dest('dist/assets/css/'))
	.pipe(livereload());
};

export function cssApp() {
	return gulp.src([
		'src/scss/font.scss',
		'src/scss/styles.scss'
	])
	.pipe(sourcemaps.init())
	.pipe(sass({ includePaths: ['node_modules'] }).on('error', sass.logError))
	.pipe(concat('app.min.css'))
	.pipe(autoprefixer())
	.pipe(minifyCSS())
	.pipe(sourcemaps.write('.'))
	.pipe(gulp.dest('dist/assets/css/'))
	.pipe(livereload());
};

export function cssImg() {
	return gulp.src([ 'src/scss/images/**' ])
		.pipe(copy('dist/assets/css/images', { prefix: 3 }));
};

export function fonts() {
	return gulp.src(['node_modules/@fortawesome/fontawesome-free/webfonts/**'])
		.pipe(copy('dist/assets/webfonts', { prefix: 4 }));
};

export function img() {
	return gulp.src(['src/img/**'])
		.pipe(copy('dist/assets/img', { prefix: 2 }));
};

export function data() {
	return gulp.src(['src/data/**'])
		.pipe(copy('dist/assets/data', { prefix: 2 }))
		.pipe(livereload());
};

export function plugins() {
	return gulp.src([
		'node_modules/@fortawesome/**',
		'node_modules/@fullcalendar/**',
		'node_modules/@highlightjs/cdn-assets/**',
		'node_modules/apexcharts/**',
		'node_modules/bootstrap/**',
		'node_modules/blueimp-file-upload/**',
		'node_modules/blueimp-tmpl/**',
		'node_modules/blueimp-gallery/**',
		'node_modules/blueimp-canvas-to-blob/**',
		'node_modules/blueimp-load-image/**',
		'node_modules/bootstrap-datepicker/**',
		'node_modules/bootstrap-daterangepicker/**',
		'node_modules/bootstrap-slider/**',
		'node_modules/bootstrap-timepicker/**',
		'node_modules/bootstrap-table/**',
		'node_modules/chart.js/**',
		'node_modules/datatables.net/**',
		'node_modules/datatables.net-bs5/**',
		'node_modules/datatables.net-autofill/**',
		'node_modules/datatables.net-autofill-bs5/**',
		'node_modules/datatables.net-buttons/**',
		'node_modules/datatables.net-buttons-bs5/**',
		'node_modules/datatables.net-colreorder/**',
		'node_modules/datatables.net-colreorder-bs5/**',
		'node_modules/datatables.net-fixedcolumns/**',
		'node_modules/datatables.net-fixedcolumns-bs5/**',
		'node_modules/datatables.net-fixedheader/**',
		'node_modules/datatables.net-fixedheader-bs5/**',
		'node_modules/datatables.net-keytable/**',
		'node_modules/datatables.net-keytable-bs5/**',
		'node_modules/datatables.net-responsive/**',
		'node_modules/datatables.net-responsive-bs5/**',
		'node_modules/datatables.net-rowgroup/**',
		'node_modules/datatables.net-rowgroup-bs5/**',
		'node_modules/datatables.net-rowreorder-bs5/**',
		'node_modules/datatables.net-scroller/**',
		'node_modules/datatables.net-scroller-bs5/**',
		'node_modules/datatables.net-select/**',
		'node_modules/datatables.net-select-bs5/**',
		'node_modules/jquery/**',
		'node_modules/jquery-migrate/**',
		'node_modules/jquery-typeahead/**',
		'node_modules/jquery-ui-dist/**',
		'node_modules/jquery.maskedinput/**',
		'node_modules/js-cookie/**',
		'node_modules/jszip/**',
		'node_modules/jvectormap-content/**',
		'node_modules/jvectormap-next/**',
		'node_modules/kbw-countdown/**',
		'node_modules/lity/**',
		'node_modules/masonry-layout/**',
		'node_modules/moment/**',
		'node_modules/pace-js/**',
		'node_modules/pdfmake/**',
		'node_modules/perfect-scrollbar/**',
		'node_modules/photoswipe/**',
		'node_modules/select-picker/**',
		'node_modules/spectrum-colorpicker2/**',
		'node_modules/summernote/**',
		'node_modules/tag-it/**'
	], { base: './node_modules/' }).pipe(copy('dist/assets/plugins', { prefix: 1 }));
};

export function jsVendor() {
  return gulp.src([
			'node_modules/pace-js/pace.min.js',
			'node_modules/jquery/dist/jquery.min.js',
			'node_modules/jquery-ui-dist/jquery-ui.min.js',
			'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
			'node_modules/perfect-scrollbar/dist/perfect-scrollbar.min.js',
			'node_modules/js-cookie/dist/js.cookie.min.js'
		])
		.pipe(sourcemaps.init())
		.pipe(concat('vendor.min.js'))
		.pipe(sourcemaps.write())
		.pipe(gulp.dest('dist/assets/js'))
		.pipe(livereload())
};

export function jsApp() {
	return gulp.src([
			'src/js/app.js',
		])
		.pipe(uglify())
    .pipe(rename('app.min.js'))  
		.pipe(gulp.dest('dist/assets/js'))
		.pipe(livereload())
};

export function jsDemo() {
	return gulp.src('src/js/demo/*')
		.pipe(copy('dist/assets/js/demo/', { prefix: 3 }))
		.pipe(livereload());
};

export function watch() {
	livereload.listen();
	gulp.watch('src/html/*.html', gulp.series(html));
	gulp.watch('src/html/*/*.html', gulp.series(html));

	gulp.watch('src/scss/*.scss', gulp.series(cssApp));
	gulp.watch('src/scss/*/*.scss', gulp.series(cssApp));

	gulp.watch('src/js/*.js', gulp.series(jsApp));
	gulp.watch('src/js/*/*.demo.js', gulp.series(jsDemo));

	gulp.watch('src/img/*', gulp.series(img));

	gulp.watch('src/data/*.json', gulp.series(data));
	gulp.watch('src/data/*/*.json', gulp.series(data));
};

export function webserver() {
	connect.server({
		name: 'Studio',
		root: ['./dist/'],
		port: 8000,
		livereload: true,
		fallback: 'index.html'
	});
};

export default gulp.series(
	html, 
	cssVendor, 
	cssApp, 
	cssImg,
	jsVendor,
	jsApp,
	jsDemo,
	img,
	data,
	fonts,
	gulp.parallel(watch, webserver)
);