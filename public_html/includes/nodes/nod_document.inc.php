<?php

	class document {

		public static $canonical = '';
		public static $content = [];
		public static $description = '';
		public static $head_tags = [];
		public static $foot_tags = [];
		public static $javascript = [];
		public static $jsenv = [];
		public static $layout = 'default';
		public static $opengraph = [];
		public static $preloads = [];
		public static $schema = [];
		public static $settings = [];
		public static $snippets = [];
		public static $style = [];
		public static $title = [];

		public static function init() {

			// Set Default OpenGraph Content
			self::$opengraph = [
				'title' => settings::get('store_name'),
				'type' => 'website',
				'url' => document::href_ilink(''),
				'image' => document::href_rlink('storage://images/logotype.png'),
			];

			// Set Default Schema Data
			self::$schema['website'] = [
				'@context' => 'https://schema.org/',
				'@type' => 'Website',
				'name' => settings::get('store_name'),
				'url' => self::ilink(''),
				'countryOfOrigin' => settings::get('store_country_code'),
			];

			self::$schema['organization'] = [
				'@context' => 'https://schema.org/',
				'@type' => 'Organization',
				'name' => settings::get('store_name'),
				'url' => self::ilink(''),
				'logo' => self::rlink(FS_DIR_STORAGE . 'images/logotype.png'),
				'email' => settings::get('store_email'),
				'availableLanguage' => array_column(language::$languages, 'name'),
			];

			event::register('before_capture', [__CLASS__, 'before_capture']);
			event::register('after_capture', [__CLASS__, 'after_capture']);
		}

		public static function before_capture() {

			//self::$snippets['nonce'] = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(32/62))), 0, 32);

			header('Content-Security-Policy: frame-ancestors \'self\';'); // Clickjacking Protection
			header('Access-Control-Allow-Origin: '. self::ilink('')); // Only allow HTTP POST data from own domain
			header('X-Frame-Options: SAMEORIGIN'); // Clickjacking Protection
			header('X-Powered-By: '. PLATFORM_NAME);

			header('Content-Security-Policy: '. implode(';', [
				"frame-ancestors 'self'", // Clickjacking Protection
					//"script-src 'nonce-". self::$snippets['nonce'] ."' 'strict-dynamic'",
					//"img-src 'self'",
					//"style-src 'self'",
					//"base-uri 'self'",
					//"form-action 'self'",
			]));

			// Default to AJAX layout on AJAX request
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				self::$layout = 'ajax';
			}

			self::$title = [settings::get('store_name')];

			// Set some snippets
			self::$snippets['language'] = language::$selected['code'];
			self::$snippets['text_direction'] = language::$selected['direction'];
			self::$snippets['charset'] = mb_http_output();
			self::$snippets['home_path'] = WS_DIR_APP;

			switch (route::$selected['endpoint']) {

				case 'backend':
					self::$snippets['template_path'] = WS_DIR_APP . 'backend/template/';
					break;

				default:
					self::$snippets['template_path'] = WS_DIR_APP . 'frontend/templates/'.settings::get('template').'/';
					break;
			}

			self::$head_tags['favicon'] = implode(PHP_EOL, [
				'<link rel="icon" href="'. self::href_rlink('storage://images/favicons/favicon.ico') .'" type="image/x-icon" sizes="32x32 48x48 64x64 96x96">',
				'<link rel="icon" href="'. self::href_rlink('storage://images/favicons/favicon-128x128.png') .'" type="image/png" sizes="128x128">',
				'<link rel="icon" href="'. self::href_rlink('storage://images/favicons/favicon-192x192.png') .'" type="image/png" sizes="192x192">',
				'<link rel="icon" href="'. self::href_rlink('storage://images/favicons/favicon-256x256.png') .'" type="image/png" sizes="256x256">',
			]);

			self::$foot_tags['jquery'] = '<script src="'. self::href_rlink('app://assets/jquery/jquery-4.0.0.min.js') .'"></script>';

			// Hreflang
			if (route::$selected['endpoint'] == 'frontend') {
				$hreflangs = [];
				foreach (language::$languages as $language) {
					if ($language['url_type'] == 'none') continue;
					$hreflangs[] = '<link rel="alternate" hreflang="'. $language['code'] .'" href="'. self::href_ilink(route::$selected['resource'], [], true, ['page', 'sort'], $language['code']) .'">';
				}
				self::$head_tags['hreflang'] = implode(PHP_EOL, $hreflangs);
			}

			// Get template settings
			if (!$template_config = include 'app://frontend/templates/'. settings::get('template') .'/config.inc.php') {
				$template_config = [];
			}

			self::$settings = settings::get('template_settings') ? json_decode(settings::get('template_settings'), true) : [];

			foreach ($template_config as $setting) {
				if (!isset(self::$settings[$setting['key']])) {
					self::$settings[$setting['key']] = $setting['default_value'];
				}
			}
		}

		public static function after_capture() {

    	// Site Tags
			$site_tags = database::query(
				"select * from ". DB_TABLE_PREFIX ."site_tags
				where status
				order by priority desc, description asc;"
			)->each(function($site_tag){

				if (!empty($site_tag['require_cookie_consent'])) {
					if (settings::get('cookie_policy') && empty($_COOKIE['cookies_accepted'])) return;
				}

				switch ($site_tag['position']) {

					case 'head':
						self::$head_tags[] = $site_tag['content'];
						break;

					case 'body':
						self::$foot_tags[] = $site_tag['content'];
						break;
				}
			});

			// JavaScript Environment

			self::$jsenv['platform'] = [
				'path' => WS_DIR_APP,
				'url' => self::ilink('f:'),
			];

			if (administrator::check_login()) {
				self::$jsenv['backend'] = [
					'path' => WS_DIR_APP . BACKEND_ALIAS .'/',
					'url' => self::ilink('b:'),
				];
			}

			self::$jsenv['template'] = [
				'settings' => self::$settings,
			];

			switch (route::$selected['endpoint']) {

				case 'backend':
					self::$jsenv['template']['url'] = WS_DIR_APP . 'backend/template/';
					break;

				default:
					self::$jsenv['template']['url'] = WS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/';
					break;
			}

			self::$jsenv['session']['id'] = session::get_id();

			document::$jsenv['currency'] = [
				'code' => &currency::$selected['code'],
				'name' => &currency::$selected['name'],
				'decimals' => &currency::$selected['decimals'],
				'prefix' => &currency::$selected['prefix'],
				'suffix' => &currency::$selected['suffix'],
			];

			document::$jsenv['language'] = [
				'code' => &language::$selected['code'],
				'name' => &language::$selected['name'],
				'decimal_point' => &language::$selected['decimal_point'],
				'thousands_separator' => &language::$selected['thousands_sep'],
			];

			self::$jsenv['customer'] = [
				'id' => !empty(customer::$data['id']) ? customer::$data['id'] : null,
				'name' => !empty(customer::$data['firstname']) ? customer::$data['firstname'] .' '. customer::$data['lastname'] : null,
				'email' => !empty(customer::$data['email']) ? customer::$data['email'] : null,
			];

			self::$head_tags[] = '<script>window._env='. json_encode(self::$jsenv, JSON_UNESCAPED_SLASHES) .'</script>';
		}

		public static function optimize(&$output) {

			// Extract styling
			$output = preg_replace_callback('#(<html[^>]*>)(.*)(</html>)#is', function($matches) use (&$stylesheets, &$styles, &$javascripts, &$javascript) {

				// Extract external stylesheets
				$stylesheets = [];

				$matches[2] = preg_replace_callback('#<link([^>]*rel="stylesheet"[^>]*)>\R*#is', function($match) use (&$stylesheets) {
					$stylesheets[] = trim($match[0]);
				}, $matches[2]);

				// Extract internal styling
				$styles = [];

				$matches[2] = preg_replace_callback('#<style[^>]*>(.+?)</style>\R*#is', function($match) use (&$styles) {
					$styles[] = trim($match[1], "\r\n");
				}, $matches[2]);

				return $matches[1] . $matches[2] . $matches[3];
			}, $output);

			// Extract javascripts
			$output = preg_replace_callback('#(<body[^>]*>)(.*)(</body>)#is', function($matches) use (&$javascripts, &$javascript) {

				// Extract external scripts
				$javascripts = [];

				$matches[2] = preg_replace_callback('#\R?<script([^>]+src="[^"]+"[^>]*)></script>\R*#is', function($match) use (&$javascripts) {
					$javascripts[] = trim($match[0]);
				}, $matches[2]);

				// Extract internal scripts
				$javascript = [];

				$matches[2] = preg_replace_callback('#<script[^>]*(?!src="[^"]+")[^>]*>(.+?)</script>\R*#is', function($match) use (&$javascript) {
					$javascript[] = trim($match[1], "\r\n");
				}, $matches[2]);

				return $matches[1] . $matches[2] . $matches[3];
			}, $output);

			// Reinsert external stylesheets
			if (!empty($stylesheets)) {
				$stylesheets = implode(PHP_EOL, $stylesheets) . PHP_EOL;
				$output = preg_replace('#</head>#', addcslashes($stylesheets . '</head>', '\\$'), $output, 1);
			}

			// Reinsert internal styles
			if (!empty($styles)) {

				// Minify internal CSS
				$search_replace = [
					'#/\*(?:.(?!/)|[^\*](?=/)|(?<!\*)/)*\*/#s' => '', // Remove comments
					'#([a-zA-Z0-9 \#=",-:()\[\]]+\{\s*\}\s*)#' => '', // Remove empty selectors
					'#\s+#' => ' ', // Replace multiple whitespace
					'#^\s+#' => ' ', // Replace leading whitespace
					'#\s*([:;{}])\s*#' => '$1',
					'#;}#' => '}',
				];

				$styles = implode(PHP_EOL, [
					'<style>',
					//'<!--/*--><![CDATA[/*><!--*/', // Do we still benefit from parser bypassing in 2024?
					preg_replace(array_keys($search_replace), array_values($search_replace), implode(PHP_EOL . PHP_EOL, $styles)),
					//'/*]]>*/-->',
					'</style>',
				]) . PHP_EOL;

				$output = preg_replace('#</head>#', addcslashes($styles . '</head>', '\\$'), $output, 1);
			}

			// Reinsert external javascripts
			if (!empty($javascripts)) {
				$javascripts = implode(PHP_EOL, $javascripts) . PHP_EOL;
				$output = preg_replace('#</body>#is', addcslashes($javascripts .'</body>', '\\$'), $output, 1);
			}

			// Reinsert internal javascript
			if (!empty($javascript)) {
				$javascript = implode(PHP_EOL, [
					'<script integrity="sha256-'. base64_encode(hash('sha256', implode(PHP_EOL, $javascript), true)) .'">',
					//'<!--/*--><![CDATA[/*><!--*/', // Do we still benefit from parser bypassing in 2024?
					//'$(document).ready(function() {',
					implode(PHP_EOL . PHP_EOL, $javascript),
					//'});',
					//'/*]]>*/-->',
					'</script>',
				]) . PHP_EOL;

				$output = preg_replace('#</body>#is', addcslashes($javascript . '</body>', '\\$'), $output, 1);
			}

			// Remove HTML comments
			$output = preg_replace_callback('#(<html[^>]*>)(.*)(</html>)#is', function($matches) {
				return preg_replace('#<!--.*?-->#ms', '', $matches[0]);
			}, $output);

			// Static domain
			if ($static_domain = settings::get('static_domain')) {
				$output = preg_replace_callback('#"https?://'. preg_quote($_SERVER['HTTP_HOST'], '#') .'(/[^"]+\.(a?png|avif|bmp|css|eot|gif|ico|jpe?g|js|map|otf|png|svg|tiff?|ttf|woff2?)(\?[^"]+)?)"#', function($matches) use ($static_domain) {
					return '"'. rtrim($static_domain, '/') .$matches[1].'"';
				}, $output);
			}
		}

		public static function render() {

			// Preloading of resources
			foreach (self::$preloads as $link => $type) {
				header('Link: <'.$link.'>; rel=preload; as='.$type, false);
			}

			stats::start_watch('rendering');

			// Set view
			switch (route::$selected['endpoint']) {

				case 'backend':
					$_page = new ent_view('app://backend/template/layouts/'.self::$layout.'.inc.php');
					break;

				default:
					$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/layouts/'.self::$layout.'.inc.php');
					break;
			}

			$_page->snippets = array_merge(self::$snippets, [
				'head_tags' => self::$head_tags,
				'style' => self::$style,
				'breadcrumbs' => breadcrumbs::render(),
				'notices' => notices::render(),
				'content' => self::$content,
				'foot_tags' => self::$foot_tags,
				'javascript' => self::$javascript,
				'important_notice' => settings::get('important_notice'),
			]);

			// Prepare title
			if (!empty(self::$title)) {

				if (!is_array(self::$title)) {
					self::$title = [self::$title];
				}

				self::$title = array_filter(self::$title);
				$_page->snippets['title'] = implode(' | ', array_reverse(self::$title));
			}

			// Add meta description
			if (!empty(self::$description)) {
				$_page->snippets['head_tags'][] = '<meta name="description" content="'. functions::escape_attr(self::$description) .'">';
			}

			// Add canonical URL
			if (!empty(self::$canonical)) {
				$_page->snippets['head_tags'][] = '<link rel="canonical" href="'. functions::escape_attr(self::$canonical) .'">';
			}

			// Prepare JSON Schema
			if (!empty(self::$schema)) {
				$_page->snippets['head_tags']['schema_json'] = implode(PHP_EOL, [
					'<script type="application/ld+json">',
					json_encode(array_values(self::$schema), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
					'</script>',
				]);
			}

			// Prepare OpenGraph Tags
			if (!empty(self::$opengraph)) {
				$_page->snippets['head_tags']['opengraph'] = implode(PHP_EOL, array_map(function($property, $content) {
					return '<meta property="og:'. functions::escape_attr($property) .'" content="'. functions::escape_attr($content) .'">';
				}, array_keys(self::$opengraph), self::$opengraph));
			}

			// Prepare internal styles
			if (!empty(self::$style)) {
				$_page->snippets['head_tags'][] = implode(PHP_EOL, [
					'<style>',
					implode(PHP_EOL . PHP_EOL, self::$style),
					'</style>',
				]);
			}

			// Prepare internal javascript
			if (!empty(self::$javascript)) {
				$_page->snippets['foot_tags'][] = implode(PHP_EOL, [
					'<script>',
					implode(PHP_EOL . PHP_EOL, self::$javascript),
					'</script>',
				]);
			}

			// Prepare snippets
			foreach ($_page->snippets as $key => $snippet) {
				if (is_array($snippet)) {
					$_page->snippets[$key] = implode(PHP_EOL, $snippet);
				}
			}

			$_page->cleanup = true;

			$output = $_page->render();

			self::optimize($output);

			stats::stop_watch('rendering');

			$output .= PHP_EOL . stats::render();

			return $output;
		}

		public static function add_head_tags($tags, $key=null) {

			if (is_array($tags)) {
				$tags = implode(PHP_EOL, $tags);
			}

			self::$head_tags[$key] = $tags;
		}

		public static function add_foot_tags($tags, $key=null) {

			if (is_array($tags)) {
				$tags = implode(PHP_EOL, $tags);
			}

			self::$foot_tags[$key] = $tags;
		}

		public static function load_style($resources, $key=null) {

			if (!is_array($resources)) {
				$resources = [$resources];
			}

			$styles = [];
			foreach ($resources as $resource) {
				if (preg_match('#^(app|storage)://#', $resource)) {
					$styles[] = '<link rel="stylesheet" integrity="sha256-'. base64_encode(hash_file('sha256', $resource, true)) .'" href="'. self::href_rlink($resource) .'">';
				} else {
					$styles[] = '<link rel="stylesheet" href="'. self::href_link($resource) .'">';
				}
			}

			self::$head_tags[$key] = implode(PHP_EOL, $styles);
		}

		public static function load_script($resources, $key=null) {

			if (!is_array($resources)) {
				$resources = [$resources];
			}

			$scripts = [];

			foreach ($resources as $resource) {
				if (preg_match('#^(app|storage)://#', $resource)) {
					$scripts[] = '<script integrity="sha256-'. base64_encode(hash_file('sha256', $resource, true)) .'" src="'. self::href_rlink($resource) .'"></script>';
				} else {
					$scripts[] = '<script src="'. self::href_link($resource) .'"></script>';
				}
			}

			self::$foot_tags[$key] = implode(PHP_EOL, $scripts);
		}

		public static function add_script($lines, $key=null) {

			if (!is_array($lines)) {
				$lines = [$lines];
			}

			self::$javascript[$key] = implode(PHP_EOL, array_map(function($line){
				return '	'.$line;
			}, $lines));
		}

		public static function add_preload($url, $type=null) {

			if (!$type) {
				$path = parse_url($url, PHP_URL_PATH);

				switch (true) {
					case (preg_match('#\.css$#', $path)):
						$type = 'style';
						break;

					case (preg_match('#\.js$#', $path)):
						$type = 'script';
						break;
				}
			}

			self::$preloads[$link] = $type;
		}

		public static function ilink($resource=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {

			switch (true) {

				case ($resource === null):
					if ($inherit_params === null) $inherit_params = true;
					$resource = route::$request;
					break;

				case (preg_match('#^b:(.*)$#', $resource, $matches)):
					$resource = WS_DIR_APP . BACKEND_ALIAS .'/'. $matches[1];
					break;

				case (preg_match('#^f:(.*)$#', $resource, $matches)):
					$resource = WS_DIR_APP . $matches[1];
					break;

				default:
					if (isset(route::$selected['endpoint']) && route::$selected['endpoint'] == 'backend') {
						$resource = WS_DIR_APP . BACKEND_ALIAS .'/'. $resource;
					} else {
						$resource = WS_DIR_APP . $resource;
					}
					break;
			}

			return (string)route::create_link($resource, $new_params, $inherit_params, $skip_params, $language_code, true);
		}

		public static function href_ilink($resource=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {
			return functions::escape_html(self::ilink($resource, $new_params, $inherit_params, $skip_params, $language_code));
		}

		public static function link($path=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {

			if (!$path) {
				$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

				if ($inherit_params === null) {
					$inherit_params = true;
				}
			}

			if (preg_match('#^(app://|storage://|'. preg_quote(DOCUMENT_ROOT, '#') .')#', $path)) {
				$path = functions::file_webpath($path);
			}

			return (string)route::create_link($path, $new_params, $inherit_params, $skip_params, $language_code, false);
		}

		public static function href_link($path=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {
			return functions::escape_html(self::link($path, $new_params, $inherit_params, $skip_params, $language_code));
		}

		public static function rlink($resource) {

			if (!$resource) {
				return '';
			}

			if (preg_match('#^app://#', $resource)) {
				$webpath = preg_replace('#^app://#', WS_DIR_APP, $resource);

			} else if (preg_match('#^storage://#', $resource)) {
				$webpath = preg_replace('#^storage://#', WS_DIR_STORAGE, $resource);

			} else {
				$webpath = preg_replace('#^('. preg_quote(DOCUMENT_ROOT, '#') .')#', '', str_replace('\\', '/', $resource));
			}

			if (is_file($resource)) {
				return self::link($webpath, ['_' => filemtime($resource)]);
			}

			return self::link($webpath);
		}

		public static function href_rlink($resource) {
			return functions::escape_html(self::rlink($resource));
		}
	}
