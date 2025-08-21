<?php

	class language {
		public static $selected = [];
		public static $languages = [];
		private static $_cache = [];
		private static $_cache_token;
		private static $_accessed_translations = [];

		public static function init() {

			// Bind selected language to session
			if (preg_match('#^'. preg_quote(WS_DIR_APP . BACKEND_ALIAS, '#') .'/#', $_SERVER['REQUEST_URI'])) {

				if (empty(session::$data['backend']['language'])) {
					session::$data['backend']['language'] = [];
				}

				self::$selected = &session::$data['backend']['language'];

			} else {

				if (!isset(session::$data['language'])) {
					session::$data['language'] = [];
				}

				self::$selected = &session::$data['language'];
			}

			// Get languages from database
			self::load();

			// Identify/set language
			self::set();

			if (!empty(self::$selected['database_connection_collation'])) {
				database::query("set names '". databse::input(strtok(self::$selected['database_connection_collation'], '_')) ."' collate '". databse::input(self::$selected['database_connection_collation']) ."';");
			}

			self::$_cache_token = cache::token('translations', ['endpoint', 'language']);

			if (!self::$_cache['translations'] = cache::get(self::$_cache_token)) {
				self::$_cache['translations'] = [];

				database::query(
					"select id, code, if(text_". self::$selected['code'] ." != '', text_". self::$selected['code'] .", text_en) as text
					from ". DB_TABLE_PREFIX ."translations
					where ". ((isset(route::$request['endpoint']) && route::$request['endpoint'] == 'backend') ? "backend = 1" : "frontend = 1") ."
					having text != '';"
				)->each(function($translation){
					self::$_cache['translations'][self::$selected['code']][$translation['code']] = $translation['text'];
				});
			}

			event::register('before_output', [__CLASS__, 'before_output']);
			event::register('shutdown', [__CLASS__, 'shutdown']);
		}

		public static function before_output() {
			header('Content-Language: '. self::$selected['code']);
		}

		public static function shutdown() {

			database::query(
				"update ". DB_TABLE_PREFIX ."translations
				set ". ((isset(route::$request['endpoint']) && route::$request['endpoint'] == 'backend') ? "backend = 1" : "frontend = 1") .",
					last_accessed = '". date('Y-m-d H:i:s') ."'
				where code in ('". implode("', '", database::input(self::$_accessed_translations)) ."');"
			);

			cache::set(self::$_cache_token, self::$_cache['translations']);
		}

		public static function load() {
			self::$languages = database::query(
				"select * from ". DB_TABLE_PREFIX ."languages
				where status
				order by priority, name;"
			)->fetch_all(null, 'code');
		}

		public static function set($code='') {

			if (!$code) {
				$code = self::identify();
			}

			if (!isset(self::$languages[$code])) {
				trigger_error('Cannot set unsupported language ('. $code .')', E_USER_WARNING);
				$code = self::identify();
			}

			if (preg_match('#^'. preg_quote(WS_DIR_APP . BACKEND_ALIAS, '#') .'/#', $_SERVER['REQUEST_URI'])) {
				session::$data['backend']['language'] = self::$languages[$code];
			} else {
				session::$data['language'] = self::$languages[$code];

				// Update customer language
				if (class_exists('customer', false) && customer::check_login()) {
					database::query(
						"update ". DB_TABLE_PREFIX ."customers
						set language_code = '". database::input(self::$data['language_code']) ."'
						where id = ". (int)session::$data['customer']['id'] ."
						limit 1;"
					);
				}
			}

			// Sort by relevance / fallback order
			uasort(self::$languages, function($a, $b){
				$pos_a = array_search($a['code'], [self::$selected['code'], settings::get('store_language_code')]);
				$pos_b = array_search($b['code'], [self::$selected['code'], settings::get('store_language_code')]);

				if ($pos_a === false && $b === false) return 0;
				else if ($pos_a === false) return 1;
				else if ($b === false) return -1;
				else return $pos_a - $pos_b;
			});

			if (!empty($_COOKIE['cookies_accepted']) || !settings::get('cookie_policy')) {
				header('Set-Cookie: language_code='. $code .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Lax', false);
			}

			// Set system locale
			if (self::$selected['locale'] && !setlocale(LC_TIME, functions::string_split(self::$selected['locale']))) {
				trigger_error('Warning: Failed setting locale '. self::$selected['locale'] .' for '. self::$selected['name'], E_USER_WARNING);
			}

			if (self::$selected['locale_intl'] && !locale_set_default(self::$selected['locale_intl'])) {
				trigger_error('Warning: Failed setting intl locale '. self::$selected['locale_intl'] .' for '. self::$selected['name'], E_USER_WARNING);
			}
		}

		public static function identify() {

			$all_languages = array_keys(self::$languages);
			$enabled_languages = [];

			foreach (self::$languages as $language) {
				if (administrator::check_login() || $language['status'] == 1) {
					$enabled_languages[] = $language['code'];
				}
			}

			// Return language by regional domain
			foreach ($enabled_languages as $language_code) {
				if (self::$languages[$language_code]['url_type'] == 'domain') {
					if (!empty(self::$languages[$language_code]['domain_name']) && preg_match('#^'. preg_quote(self::$languages[$language_code]['domain_name'], '#') .'$#', $_SERVER['HTTP_HOST'])) {
						return $language_code;
					}
				}
			}

			// Return language from URI query
			if (!empty($_GET['language'])) {
				if (in_array($_GET['language'], $all_languages)) {
					return $_GET['language'];
				}
			}

			// Return language from URI path
			$code = current(explode('/', substr($_SERVER['REQUEST_URI'], strlen(WS_DIR_APP))));
			if (in_array($code, $all_languages)) return $code;

			// Return language from session
			if (isset(self::$selected['code']) && in_array(self::$selected['code'], $all_languages)){
				return self::$selected['code'];
			}

			// Return language from cookie
			if (isset($_COOKIE['language_code']) && in_array($_COOKIE['language_code'], $all_languages)){
				return $_COOKIE['language_code'];
			}

			// Return language from country (TLD)
			if (preg_match('#\.([a-z]{2})$#', $_SERVER['HTTP_HOST'], $matches)) {

				$country = database::query(
					"select * from ". DB_TABLE_PREFIX ."countries
					where iso_code_2 = '". database::input(strtoupper($matches[1])) ."'
					limit 1;"
				)->fetch();

				if ($country && in_array($country['language_code'], $enabled_languages)){
					return $country['language_code'];
				}
			}

			// Return language from browser request headers
			if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				$browser_locales = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			} elseif (isset($_SERVER['LC_CTYPE'])) {
				$browser_locales = explode(',', $_SERVER['LC_CTYPE']);
			} else {
				$browser_locales = [];
			}

			foreach ($browser_locales as $browser_locale) {
				if (preg_match('#('. implode('|', array_keys(self::$languages)) .')-?.*#', $browser_locale, $reg)) {
					if (!empty($reg[1]) && in_array($reg[1], $enabled_languages)) {
						return $reg[1];
					}
				}
			}

			// Return default language
			if (in_array(settings::get('default_language_code'), $all_languages)) {
				return settings::get('default_language_code');
			}

			// Return system language
			if (in_array(settings::get('store_language_code'), $all_languages)) {
				return settings::get('store_language_code');
			}

			// Return first language
			return (!empty($enabled_languages)) ? $enabled_languages[0] : $all_languages[0];
		}

		public static function translate($code, $default='', $language_code='') {

			$code = strtolower($code);

			self::$_accessed_translations[] = $code;

			if (!$language_code) {
				$language_code = self::$selected['code'];
			}

			if (!$language_code || empty(self::$languages[$language_code])) {
				trigger_error('Unknown language code for translation ('. $language_code .')', E_USER_WARNING);
				return;
			}

			// Return from cache
			if (isset(self::$_cache['translations'][$language_code][$code])) {
				return self::$_cache['translations'][$language_code][$code];
			}

			// Get translation from database
			$translation = database::query(
				"select id, text_en, `text_". $language_code ."` from ". DB_TABLE_PREFIX ."translations
				where code = '". database::input($code) ."'
				limit 1;"
			)->fetch();

			// Create translation if it doesn't exist
			if (!$translation) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."translations
					(code, text_en, html, created_at, updated_at)
					values ('". database::input($code) ."', '". database::input($default, true) ."', '". (($default != strip_tags($default)) ? 1 : 0) ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
				);
			}

			// Return translation
			if (!empty($translation['text_'.$language_code])) {
				return self::$_cache['translations'][$language_code][$code] = $translation['text_'.$language_code];
			}

			// If we have an english translation
			if (!empty($translation['text_en'])) {

				// Find same english translation by different key
				$secondary_translation = database::query(
					"select id, text_en, `text_". $language_code ."` from ". DB_TABLE_PREFIX ."translations
					where text_en = '". database::input($translation['text_en']) ."'
					and (text_en is not null and text_en != '')
					and (text_". self::$selected['code'] ." is not null and text_". self::$selected['code'] ." != '')
					limit 1;"
				)->fetch();

				if ($secondary_translation) {
					database::query(
						"update ". DB_TABLE_PREFIX ."translations
						set `text_". $language_code ."` = '". database::input($translation['text_'.$language_code], true) ."',
						updated_at = '". date('Y-m-d H:i:s') ."'
						where text_en = '". database::input($translation['text_en']) ."'
						and text_". self::$selected['code'] ." = '';"
					);

					return self::$_cache['translations'][$language_code][$code] = $secondary_translation['text_'.$language_code];
				}

				// Return english translation
				return self::$_cache['translations'][$language_code][$code] = $translation['text_en'];
			}

			// Return default translation
			return self::$_cache['translations'][$language_code][$code] = $default;
		}

		public static function number_format($number, $decimals=0) {
			return number_format((float)$number, (int)$decimals, self::$selected['decimal_point'], self::$selected['thousands_sep']);
		}

		public static function strftime($format, $timestamp=null) {
			trigger_error('Method language::strftime() is deprecated. Instead, use functions::datetime_format()', E_USER_DEPRECATED);
			return functions::datetime_format($format, $timestamp);
		}

		public static function convert_characters($variable, $from_charset=null, $to_charset=null) {

			if (!$from_charset) {
				$from_charset = mb_internal_encoding();
			}

			if (!$to_charset) {
				$to_charset = mb_internal_encoding();
			}

			if ($from_charset == $to_charset) {
				return $variable;
			}

			if (!mb_convert_variables($to_charset, $from_charset, $variable)) {
				trigger_error('Could not encode variable from '. $from_charset .' to '. $to_charset, E_USER_WARNING);
				return false;
			}

			return $variable;
		}
	}
