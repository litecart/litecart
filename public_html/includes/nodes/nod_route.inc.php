<?php

	class route {

		private static $_links_cache = [];
		private static $_links_cache_token;
		private static $_routes = [];
		public static $selected = [];
		public static $request = '';

		public static function init() {

			// Neutralize request path (removes logical prefixes)
			self::$request = self::strip_url_logic($_SERVER['REQUEST_URI']);

			// Load cached links (URL rewrites)
			self::$_links_cache_token = cache::token('links', ['site', 'endpoint', 'language'], 'file', 900);

			if (!self::$_links_cache = cache::get(self::$_links_cache_token)) {
				self::$_links_cache = [];
			}

			event::register('after_capture', [__CLASS__, 'after_capture']);
		}

		public static function after_capture() {
			cache::set(self::$_links_cache_token, self::$_links_cache);
		}

		######################################################################

		public static function load($pattern) {

			foreach (functions::file_search($pattern) as $file) {

				$routes = include $file;
				if (!$routes) continue;

				foreach ($routes as $resource => $route) {
					self::add($resource, $route);
				}
			}
		}

		public static function add($resource, $route) {

			if (strpos($resource, ':') === false) {
				if (!preg_match('#^\w:#', $resource)) {
					$resource = 'f:'.$resource;
				}
			}

			switch (true) {

				case (preg_match('#^b:#', $resource)):
					$route['endpoint'] = 'backend';
					break;

				default:
					$route['endpoint'] = 'frontend';
					break;
			}

			if (!isset($route['patterns'])) {
				$route['patterns'] = [$route['pattern']];
			}

			self::$_routes[] = [
				'resource' => $resource,
				'patterns' => fallback($route['patterns'], ''),
				'endpoint' => fallback($route['endpoint'], 'frontend'),
				'controller' => fallback($route['controller']),
				'params' => fallback($route['params'], []),
				'options' => fallback($route['options'], []),
				'rewrite' => fallback($route['rewrite']),
			];
		}

		// Resolve the request to a route
		public static function identify() {

			// Step through each route
			foreach (self::$_routes as $route) {

				// Does any pattern of the route match the request?
				foreach ($route['patterns'] as $pattern) {

					if (preg_match($pattern, self::$request)) {

						// Resolve resource logic
						if (preg_match('#\*#', $route['resource'])) {
							$route['resource'] = preg_replace_callback('#^(\w:).*$#', function($matches){
								return fallback($matches[1], 'f:') . preg_replace('#^'. preg_quote(ltrim(BACKEND_ALIAS . '/', '/'), '#') .'#', '', parse_url(self::$request, PHP_URL_PATH));
							}, $route['resource']);
						}

						// Resolve controller logic
						if (is_string($route['controller'])) {
							$route['controller'] = preg_replace($pattern, $route['controller'], self::$request);
						}

						// Resolve query params logic
						if (!empty($route['params'])) {
							parse_str(preg_replace($pattern, $route['params'], self::$request), $params);
							$_GET = array_filter(array_merge($_GET, $params));
						}

						return self::$selected = $route;
					}
				}
			}
		}

		public static function process() {

			if (!self::$selected) {
				self::identify();
			}

			// Forward to rewritten URL (if necessary)
			if (self::$selected) {

				$requested_url = document::link($_SERVER['REQUEST_URI']);
				$rewritten_url = document::ilink(self::$selected['resource'], $_GET);

				if ($requested_url != $rewritten_url) {

					$do_redirect = true;

					// Don't forward if there is HTTP POST data
					if (file_get_contents('php://input') != '') {
						$do_redirect = false;
					}

					// Don't forward if requested not to
					if (isset(self::$selected['options']['redirect']) && self::$selected['options']['redirect'] != true) {
						$do_redirect = false;
					}

					// Don't forward if there are notices in stack
					if (!empty(notices::$data)) {
						foreach (notices::$data as $notices) {
							if (!$notices) {
								$do_redirect = false;
								break;
							}
						}
					}

					if ($do_redirect) {

						// Send HTTP 302 if it's the start page
						if (parse_url(self::$request, PHP_URL_PATH) == WS_DIR_APP) {
							header('Location: '. $rewritten_url, true, 302);
							exit;
						}

						header('Location: '. $rewritten_url, true, 301);
						exit;
					}
				}
			}

			// Execute a file controller
			if (!empty(self::$selected['controller']) && is_string(self::$selected['controller'])) {

				if (is_file(self::$selected['controller'])) {

					(function(){
						include func_get_arg(0);
					})(self::$selected['controller']);

					return;
				}
			}

			// Return a static file
			$request_path = functions::file_resolve_path(parse_url(self::$request, PHP_URL_PATH));

			// Create whitelist of static folder content
			$static_folders = [
				'assets/',
			];

			// Tunnel an asset stored in an add-on
			if (preg_match('#^('. implode('|', array_map(function($folder) { return preg_quote($folder, '#'); }, $static_folders)) .')#', $request_path)
			 && is_file('app://'.$request_path) && preg_match('#\.(a?png|avif|bmp|css|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$#', pathinfo($request_path, PATHINFO_BASENAME))) {

				if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime('app://'.$request_path)) {
					header('HTTP/1.1 304 Not Modified');
					exit;
				}

				if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
					foreach (preg_split('#\s*,\s*#', $_SERVER['HTTP_IF_NONE_MATCH'], -1, PREG_SPLIT_NO_EMPTY) as $potential_match) {
						if (trim($potential_match, '"') == md5_file('app://'.$request_path)) {
							header('HTTP/1.1 304 Not Modified');
							exit;
						}
					}
				}

				switch (pathinfo($request_path, PATHINFO_EXTENSION)) {

					case 'css': // Not supported by mime_content_type()
						header('Content-Type: text/css; charset='. mb_http_output());
						break;

					case 'js': // Not supported by mime_content_type()
						header('Content-Type: text/javascript; charset='. mb_http_output());
						break;

					default:
						header('Content-Type: '. mime_content_type('app://'.$request_path));
						break;
				}

				header('Content-Length: '. filesize('app://'.$request_path));
				header('Etag: '. md5_file('app://'.$request_path));
				header('Last-Modified: '. gmdate('D, d M Y H:i:s', filemtime('app://'.$request_path)) .' GMT');
				header('Pragma: cache');
				header('Cache-Control: public, max-age=604800');	// 7 days
				header('Expires: '. gmdate('D, d M Y H:i:s', strtotime('+7 days')) .' GMT');
				header('X-Content-Type-Options: nosniff');
				header('X-Frame-Options: SAMEORIGIN');

				readfile('app://'.$request_path);
				exit;
			}

			// Display error document
			http_response_code(404);

			$request = new ent_link(document::link());

			// Don't return an error document for content with a defined extension (presumably static)
			if (preg_match('#\.[a-z]{2,4}$#', $request->path) && !preg_match('#\.(html?|php)$#', $request->path)) exit;

			$not_found_file = 'storage://logs/not_found.log';

			$lines = is_file($not_found_file) ? file($not_found_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
			$lines[] = $request->path;
			$lines = array_unique($lines);

			sort($lines);

			if (count($lines) >= 100) {

				$email = new ent_email();
				$email->add_recipient(settings::get('store_email'))
					->set_subject('[Not Found Report] '. settings::get('store_name'))
					->add_body(
						wordwrap("This is a list of the last 100 requests made to your website that did not have a destination. Most of these reports usually contain scans and attacks by evil robots. But some URLs may be indexed by search engines requiring a redirect to a proper destination.", 72, "\r\n") . "\r\n\r\n" .
						PLATFORM_NAME .' '. PLATFORM_VERSION ."\r\n\r\n" .
						implode("\r\n", array_map($lines, 'wordwrap'))
					)
					->send();

				file_put_contents($not_found_file, '');

			} else {
				file_put_contents($not_found_file, implode(PHP_EOL, $lines) . PHP_EOL);
			}

			include 'app://frontend/pages/error_document.inc.php';
			include 'app://includes/app_footer.inc.php';
			exit;
		}

		public static function strip_url_logic($path) {

			if (!$path) {
				return '';
			}

			// Sanitize URL
			$path = filter_var($path, FILTER_SANITIZE_URL);

			foreach ([
				'#[:\'\*"]#' => '', // Remove bad characters
				'#\.#' => '', // Remove hidden resource definition
				'#//+#' => '/', // Replace multiple directory separators
			] as $pattern => $replace) {
				$path = preg_replace($pattern, $replace, $path);
			}

			$path = functions::file_resolve_path($path);

			// Remove language prefix
			if ($path = urldecode(parse_url($path, PHP_URL_PATH))) {
				$path = preg_replace('#^'. WS_DIR_APP . '(index\.php/)?(('. implode('|', array_keys(language::$languages)) .')(/|$))?#', '', $path);
			}

			if (!$path) {
				return '';
			}

			return $path;
		}

		public static function create_link($path=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null, $rewrite=false) {

			if (!$language_code) {
				$language_code = language::$selected['code'];
			}

			$link = new ent_link((string)$path);

			if ($path === null && $inherit_params === null) {
				$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
				$inherit_params = true;
			}

			// Set params that are inherited from the current page
			if ($inherit_params === true) {
				foreach ($_GET as $key => $value) {
					if (in_array($key, ['country', 'currency', 'language'])) continue;
					$link->set_query($key, $value);
				}

			} else if (is_array($inherit_params)) {
				foreach ($_GET as $key => $value) {
					if (in_array($key, $inherit_params)) {
						$link->set_query($key, $value);
					}
				}
			}

			// Unset params that are to be skipped from the link
			if (is_string($skip_params)) {
				$skip_params = [$skip_params];
			}

			foreach ($skip_params as $key) {
				if (isset($link->query[$key])) {
					$link->unset_query($key);
				}
			}

			// Set new params (overwrites any existing inherited params)
			if (!empty($new_params)) {
				foreach ($new_params as $key => $value) {
					$link->set_query($key, $value);
				}
			}

			// Rewrite URL
			if ($rewrite && $link->host == $_SERVER['HTTP_HOST']) {
				if (preg_match('#^'. WS_DIR_APP .'#', $link->path)) {
					return self::rewrite($link, $language_code);
				}
			}

			return $link;
		}

		public static function rewrite(ent_link $link, $language_code=null) {

			if ($link->host != $_SERVER['HTTP_HOST']) {
				return $link;
			}

			if (!$language_code) {
				$language_code = language::$selected['code'];
			}

			if (!empty($link->query['language'])) {
				$language_code = $link->query['language'];
			}

			if (!in_array($language_code, array_keys(language::$languages))) {
				$language_code = language::$selected['code'];
			}

			$checksum = crc32((string)$link);

			if (isset(self::$_links_cache[$language_code][$checksum])) {
				return self::$_links_cache[$language_code][$checksum];
			}

			// Strip logic from string
			$ipath = self::strip_url_logic($link->path);

			if (!preg_match('#^\w:#', $ipath)) {
				$ipath = 'f:'.$ipath;
			}

			// Rewrite link
			foreach (self::$_routes as $ilink => $route) {
				if (preg_match('#^'. strtr(preg_quote($ilink, '#'), ['\\*' => '.+', '\\?' => '.', '\\{' => '(', '\\}' => ')', ',' => '|']) .'$#i', $ipath)) { // Use preg_match() as fnmatch() does not support GLOB_BRACE
					if (isset($route['rewrite']) && is_callable($route['rewrite'])) {
						if ($rewritten_link = call_user_func_array($route['rewrite'], [$link, $language_code])) {
							$link = $rewritten_link;
						}
					}
				}
			}

			// Detect URL rewrite support
			if (isset($_SERVER['HTTP_MOD_REWRITE']) && filter_var($_SERVER['HTTP_MOD_REWRITE'], FILTER_VALIDATE_BOOLEAN)) { // PHP-FPM
				$use_rewrite = true;

			} else if (isset($_SERVER['REDIRECT_HTTP_MOD_REWRITE']) && filter_var($_SERVER['REDIRECT_HTTP_MOD_REWRITE'], FILTER_VALIDATE_BOOLEAN)) {  // Fast CGI
				$use_rewrite = true;

			} else if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
				$use_rewrite = true;

			} else if (preg_match('#(apache)#i', $_SERVER['SERVER_SOFTWARE'])) {
				$use_rewrite = true;

			} else {
				$use_rewrite = false;
			}

			// Set language to URL
			switch (language::$languages[$language_code]['url_type']) {

				case 'path':
					$link->path = $language_code .'/'. ltrim($link->path, '/');
					break;

				case 'domain':
					$link->host = language::$languages[$language_code]['domain_name'];
					break;
			}

			if (isset($link->query['language'])) {
				$link->unset_query('language');
			}

			// Set base (/index.php/ or /)
			if ($use_rewrite) {
				$link->path = WS_DIR_APP . ltrim($link->path, '/');
			} else {
				$link->path = WS_DIR_APP . 'index.php/' . ltrim($link->path, '/');
			}

			return self::$_links_cache[$language_code][$checksum] = (string)$link;
		}
	}
