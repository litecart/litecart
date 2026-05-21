<?php

	/*
		Holds a multilingual payload (locale => string) and renders the active
		locale on stringification with a documented fallback chain.

		Example:
		    $name = new type_translation([
		        'en' => 'Rubber Ducks',
		        'de' => 'Gummienten',
		        'sv' => 'Gummiankor',
		    ]);
		    echo $name;            // current locale (language::$selected['code'])
		    echo $name->in('de');  // 'Gummienten'
		    isset($name['de']);    // true — ArrayAccess for $row['name']['de']-style callers

		Fallback chain documented at __toString().
	*/

	class type_translation implements \JsonSerializable, \ArrayAccess {

		private $_translations = [];

		public function __construct(string|array|type_translation $input = '', ?string $locale = null) {

			if ($input instanceof type_translation) {
				$this->_translations = $input->_translations;
				return;
			}

			if (is_array($input)) {
				// Plain {locale => string} map.
				foreach ($input as $code => $value) {
					if (is_string($code) && is_scalar($value)) {
						$this->_translations[$code] = (string)$value;
					}
				}
				return;
			}

			if (is_string($input) && $input !== '') {
				// Seed the requested locale (or the active locale) with the string.
				$code = $locale ?: ($this->_active_locale() ?: 'en');
				$this->_translations[$code] = $input;
			}
		}

		/*
			Returns the value for a specific locale, walking the same fallback
			chain as __toString. Always returns a string (never null), so callers
			don't have to coalesce.
		*/
		public function in(string $locale): string {
			if (isset($this->_translations[$locale]) && $this->_translations[$locale] !== '') {
				return $this->_translations[$locale];
			}
			return $this->_fallback($locale);
		}

		public function set(string $locale, string $value): self {
			$this->_translations[$locale] = $value;
			return $this;
		}

		public function has(string $locale): bool {
			return isset($this->_translations[$locale]) && $this->_translations[$locale] !== '';
		}

		public function locales(): array {
			return array_keys(array_filter($this->_translations, fn($v) => $v !== ''));
		}

		/*
			Fallback chain for __toString:
			1. Active locale (language::$selected['code'])
			2. Store default locale (settings::get('store_language_code'))
			3. 'en' as final declared fallback (LiteCart's seed locale)
			4. First populated entry (defensive — shouldn't happen on a sane install)
			5. Empty string
		*/
		public function __toString(): string {
			return $this->_fallback($this->_active_locale());
		}

		private function _fallback(string $requested_locale = ''): string {

			$store_locale = (string)settings::get('store_language_code');

			foreach (array_unique(array_filter([
				$requested_locale,
				$store_locale,
				'en',
			])) as $code) {
				if (!empty($this->_translations[$code])) {
					return $this->_translations[$code];
				}
			}

			// Defensive: return the first non-empty entry if the documented chain misses.
			foreach ($this->_translations as $value) {
				if ($value !== '') return $value;
			}

			return '';
		}

		private function _active_locale(): string {
			if (isset(language::$selected['code']) && is_string(language::$selected['code'])) {
				return language::$selected['code'];
			}
			return '';
		}

		public function jsonSerialize(): mixed {
			// Matches the existing JSON column shape — {locale => string}.
			return $this->_translations;
		}

		// ArrayAccess — preserves $row['name']['de']-style read patterns from
		// pre-type_translation call sites without forcing a sweep.
		public function offsetExists($offset): bool {
			return $this->has((string)$offset);
		}

		public function offsetGet($offset): mixed {
			return $this->in((string)$offset);
		}

		public function offsetSet($offset, $value): void {
			if ($offset === null) {
				trigger_error('type_translation requires an explicit locale key', E_USER_WARNING);
				return;
			}
			$this->set((string)$offset, (string)$value);
		}

		public function offsetUnset($offset): void {
			unset($this->_translations[(string)$offset]);
		}
	}
