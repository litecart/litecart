<?php

	/*
		Example usage:
		$url = new type_url('//domain.tld/path/to/file');
		$url->host = 'newdomain.tld';
		echo $url;
	*/

	class type_url implements \JsonSerializable {

		private $_components;
		private $_serialized;

		public function __construct(string|type_url $url='') {

			$this->reset();

			if ($url instanceof type_url) {

				foreach (array_keys($this->_components) as $component) {
					$this->$component = $url->$component;
				}

			} else {

				$components = is_array($url) ? $url : parse_url($url);

				if ($components) {
					foreach ($components as $component => $value) {
						$this->$component = $value;
					}
				}
			}
		}

		public function __isset(string $component): bool {
			return $this->__get($component) !== null;
		}

		public function __unset(string $component): void {
			$this->__set($component, null);
		}

		public function __get(string $component): mixed {

			if (!isset($this->_components[$component])) {
				trigger_error("Unknown link component ($component)", E_USER_WARNING);
				return null;
			}

			if (!empty($this->_components[$component])) {
				return $this->_components[$component];
			}

			// Set defaults
			if (in_array($component, ['scheme', 'host', 'port', 'path'])) {
				$this->$component = '';
			}

			return $this->_components[$component];
		}

		public function __set(string $component, mixed $value): void {

			if (!isset($this->_components[$component])) {
				trigger_error("Unknown link component ($component)", E_USER_WARNING);
				return;
			}

			switch ($component) {

				case 'scheme':

					if (!$value) {
						if ($this->host == $_SERVER['HTTP_HOST']) {
							if (isset($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) {
								$value = 'https';
							} else if	(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
								$value = 'https';
							} else {
								$value = 'http';
							}
						} else {
							$value = 'http';
						}
					}

					break;

				case 'host':

					if (!$value) {
						$value = $_SERVER['HTTP_HOST'];
					}

					//if (function_exists('idn_to_ascii')) {
					//  $value = idn_to_ascii($value, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
					//}

					break;

				case 'path':

					// Pop path
					if (str_contains($value, '..')) {

						$parts = array_filter(explode('/', $value), 'strlen');
						$absolutes = [];

						foreach ($parts as $part) {
							if ('.' == $part) continue;
							if ('..' == $part) {
								array_pop($absolutes);
							} else {
								$absolutes[] = $part;
							}
						}

						$value = '/' . implode('/', $absolutes);
					}

					break;

				case 'query':

					if (!is_array($value)) {
						parse_str($value, $value);
					}

					break;
			}

			if ($this->_components[$component] != $value) {
				$this->_components[$component] = $value;
				$this->_serialized = '';
			}

			return;
		}

		public function __toString(): string {

			if (!empty($this->_serialized)) return $this->_serialized;

			$output = $this->scheme .'://';

			if (!empty($this->user)) {
				$output .= $this->user;

				if (!empty($this->pass)) {
					$output .= ':'.$this->pass;
				}

				$output .= '@';
			}

			$output .= $this->host;

			if (!empty($this->port)) {
				if ($this->scheme == 'https' && $this->port != 443) {
					$output .= ':'.$this->port;
				} else if ($this->scheme == 'http' && $this->port != 80) {
					$output .= ':'.$this->port;
				} else {
					$output .= ':'.$this->port;
				}
			}

			// Relative to absolute path
			if (substr($this->path, 0, 1) != '/') {
				if (substr($this->scheme, 0, 4) == 'http' && $this->host == $_SERVER['HTTP_HOST']) {
					$output .= WS_DIR_APP;
				} else {
					$output .= '/';
				}
			}

			$output .= $this->path;

			if (!empty($this->_components['query'])) {
				$output .= '?'.preg_replace('#(=(?=(&|$)))#', '', http_build_query($this->_components['query'], '', '&'));
			}

			if (!empty($this->_components['fragment'])) {
				$output .= '#'.$this->_components['fragment'];
			}

			return $output;
		}

		public function jsonSerialize(): mixed {
			return $this->__toString();
		}

		// Workaround as overloaded array items cannot be set
		public function set_query($key, $value): void {

			$this->_components['query'][$key] = is_object($value) ? (string)$value : $value;

			return;
		}

		// Workaround as overloaded array items cannot be unset
		public function unset_query($key): void {

			if (is_array($key)) {

				$array_diff_recursive = function($array1, $array2) use (&$array_diff_recursive) {
					$result = [];
					foreach ($array1 as $key => $value) {
						if (array_key_exists($key, $array2)) {
							if (is_array($value)) {
								if ($tmp = $array_diff_recursive($value, $array2[$key])) {
									$result[$key] = $tmp;
								}
							} else {
								if ($value != $array2[$key]) {
									$result[$key] = $value;
								}
							}
							continue;
						}
						$result[$key] = $value;
					}
					return $result;
				};

				$this->_components['query'] = $array_diff_recursive($this->_components['query'], $key);

			} else {
				unset($this->_components['query'][$key]);
			}

			return;
		}

		public function reset(): void {
			$this->_components = [
				'scheme' => '',
				'host' => '',
				'user' => '',
				'pass' => '',
				'port' => '',
				'path' => '',
				'query' => [],
				'fragment' => '',
			];
		}
	}
