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
			event::register('before_capture', [__CLASS__, 'before_capture']);
			event::register('after_capture', [__CLASS__, 'after_capture']);
		}

		public static function before_capture() {

			header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload'); // HSTS
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

			switch (fallback(route::$selected['endpoint'])) {

				case 'backend':
					self::$snippets['template_path'] = WS_DIR_APP . 'backend/template/';
					break;

				default:
					self::$snippets['template_path'] = WS_DIR_APP . 'frontend/templates/'.settings::get('template').'/';
					break;
			}

			// Alert errors to administrator
			if (administrator::check_login()) {
				self::add_head_tags(implode(PHP_EOL, [
					'<script>let _alertedErrors=0;window.onerror=(c,r,a,p)=>{_alertedErrors++<5&&alert(c+" in "+r.split("/").pop().split("?")[0]+" on line "+a)};</script>',
				]), 'alert_errors');
			}

			// Wait For (Mini version)
			self::add_head_tags(implode(PHP_EOL, [
				'<script>window.waitFor=window.waitFor||((i,o)=>{void 0!==window[i]?o(window[i]):setTimeout((()=>waitFor(i,o)),50)});</script>',
			]), 'waitFor');

			// Load jQuery
			self::load_script('app://assets/jquery/jquery-4.0.0.min.js', 'jquery');

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

			// JavaScript Environment

			self::$jsenv['platform'] = [
				'path' => WS_DIR_APP,
				'url' => self::ilink('f:'),
			];

			self::$jsenv['template'] = [
				'settings' => self::$settings,
			];

			switch (fallback(route::$selected['endpoint'])) {

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
				'id' => customer::check_login() ? customer::$data['id'] : null,
				'name' => customer::$data['firstname'] ? customer::$data['firstname'] .' '. customer::$data['lastname'] : null,
				'email' => customer::$data['email'] ?: null,
			];

			self::$head_tags[] = '<script>window._env='. json_encode(self::$jsenv, JSON_UNESCAPED_SLASHES) .'</script>';
		}

		public static function optimize(&$output) {

			// Extract styling
			$output = preg_replace_callback('#(<html[^>]*>)(.*)(</html>)#is', function($matches) use (&$stylesheets, &$style, &$javascripts, &$javascript) {

				// Extract external stylesheets
				$stylesheets = [];

				$matches[2] = preg_replace_callback('#<link([^>]*rel="stylesheet"[^>]*)>\R*#is', function($match) use (&$stylesheets) {
					$stylesheets[] = trim($match[0]);
				}, $matches[2]);

				// Extract internal styling
				$style = [];

				$matches[2] = preg_replace_callback('#<style[^>]*>(.+?)</style>\R*#is', function($match) use (&$style) {
					$style[] = trim($match[1], "\r\n");
				}, $matches[2]);

				return $matches[1] . $matches[2] . $matches[3];
			}, $output);

			// Extract javascripts
			$output = preg_replace_callback('#(<body[^>]*>)(.*)(</body>)#is', function($matches) use (&$javascripts, &$javascript) {

				// Extract external scripts
				$javascripts = [];

				$matches[2] = preg_replace_callback('#\R?<script([^>]+src="[^"]+"[^>]*)></script>\R*#is', function($match) use (&$javascripts) {
					$javascripts[] = '<script ' . trim($match[1]) .'></script>';
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
			if (!empty($style)) {

				// Convert to string
				$style = implode(PHP_EOL . PHP_EOL, $style);

				// Minify internal CSS
				foreach([
					'#/\*(?:.(?!/)|[^\*](?=/)|(?<!\*)/)*\*/#s' => '', // Remove comments
					'#([a-zA-Z0-9 \#=",-:()\[\]]+\{\s*\}\s*)#' => '', // Remove empty selectors
					'#\s+#' => ' ', // Replace multiple whitespace
					'#^\s+#' => ' ', // Replace leading whitespace
					'#\s*([,:;{}])\s*#' => '$1', // Remove whitespace around delimiters
					'#;}#' => '}', // Remove trailing semicolons before closing brackets
				] as $search => $replace) {
					$style = preg_replace($search, $replace, $style);
				}

				$style = implode(PHP_EOL, [
					//'<!--/*--><![CDATA[/*><!--*/', // Do we still benefit from parser bypassing?
					$style,
					//'/*]]>*/-->',
				]);

				// Build integrity hash
				$checksum = hash('sha256', $style, true);

				// Prepare style tag
				$style = implode(PHP_EOL, [
					'<style integrity="sha256-'. base64_encode($checksum) .'" crossorigin="anonymous">',
					$style,
					'</style>',
				]);

				// Insert style tag before </head>
				$output = preg_replace('#</head>#', addcslashes($style, '\\$') . PHP_EOL . '</head>', $output . PHP_EOL, 1);
			}

			// Reinsert external javascripts
			if (!empty($javascripts)) {
				$javascripts = implode(PHP_EOL, $javascripts) . PHP_EOL;
				$output = preg_replace('#</body>#is', addcslashes($javascripts .'</body>', '\\$'), $output, 1);
			}

			// Reinsert internal javascript
			if (!empty($javascript)) {

				// Convert to string
				$javascript = implode(PHP_EOL, [
					//'<!--/*--><![CDATA[/*><!--*/', // Do we still benefit from parser bypassing?
					'+waitFor(\'jQuery\', function($) {',
					implode(PHP_EOL . PHP_EOL, $javascript),
					'});',
					//'/*]]>*/-->',
				]);

				// Build integrity hash
				$checksum = hash('sha256', $javascript, true);

				// Prepare script tag
				$javascript = implode(PHP_EOL, [
					'<script integrity="sha256-'. base64_encode($checksum) .'" crossorigin="anonymous">',
					$javascript,
					'</script>',
				]) . PHP_EOL;

				// Insert javascript before </body>
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
			switch (fallback(route::$selected['endpoint'])) {

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
					$styles[] = '<link rel="stylesheet" integrity="sha256-'. base64_encode(hash_file('sha256', $resource, true)) .'" crossorigin="anonymous" href="'. self::href_rlink($resource) .'">';
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
					$scripts[] = '<script defer integrity="sha256-'. base64_encode(hash_file('sha256', $resource, true)) .'" crossorigin="anonymous" src="'. self::href_rlink($resource) .'"></script>';
				} else {
					$scripts[] = '<script src="'. self::href_link($resource) .'"></script>';
				}
			}

			self::$foot_tags[$key] = implode(PHP_EOL, $scripts);
		}

		public static function add_script($lines, $key=null) {

			if (is_array($lines)) {
				$lines = implode(PHP_EOL, $lines);
			}

			if (!preg_match('#^( |\t)#', $lines)) {
				$lines = preg_replace('#^#m', "\t", $lines);
			}

			self::$javascript[$key] = $lines;
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
