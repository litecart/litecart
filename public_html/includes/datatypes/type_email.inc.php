<?php

	/*
		Example usage:
		$email = new type_email('"John Doe" <john@example.com>');
		echo $email->local;     // john
		echo $email->domain;    // example.com
		echo $email->name;      // John Doe
		echo $email;            // "John Doe" <john@example.com>
		echo $email->address;   // john@example.com (without display name)

		Drop-in for ent_email recipient/sender arrays:
		$email->jsonSerialize(); // ['email' => 'john@example.com', 'name' => 'John Doe']
	*/

	class type_email implements \JsonSerializable {

		private $_components;

		public function __construct($input='') {

			$this->reset();

			if ($input instanceof type_email) {
				foreach (array_keys($this->_components) as $component) {
					$this->$component = $input->$component;
				}
				return;
			}

			if (is_array($input)) {
				foreach ($input as $component => $value) {
					$this->$component = $value;
				}
				return;
			}

			if (!is_string($input) || $input === '') {
				return;
			}

			// Parse "Name <addr@host>" or "<addr@host>" or "addr@host"
			if (preg_match('#^\s*("?)(?<name>.*?)\1\s*<(?<address>[^>]+)>\s*$#', $input, $matches)) {
				$this->name = trim($matches['name']);
				$this->address = trim($matches['address']);
			} else {
				$this->address = trim($input);
			}
		}

		public function __isset($component) {
			return !empty($this->_components[$component]);
		}

		public function __unset($component) {
			$this->__set($component, '');
		}

		public function __get($component) {

			switch ($component) {

				case 'address':
					if (!empty($this->_components['local']) && !empty($this->_components['domain'])) {
						return $this->_components['local'] .'@'. $this->_components['domain'];
					}
					return '';

				case 'is_valid':
					return (bool)$this->__get('address')
						&& (bool)preg_match(
							'#^([a-zA-Z0-9])+([a-zA-Z0-9\+\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$#',
							$this->__get('address')
						);

				case 'local':
				case 'domain':
				case 'name':
					return $this->_components[$component] ?? '';
			}

			trigger_error("Unknown email component ($component)", E_USER_WARNING);
			return null;
		}

		public function __set($component, $value) {

			switch ($component) {

				case 'address':
				case 'email':
					$value = trim((string)$value);
					$value = filter_var($value, FILTER_SANITIZE_EMAIL);

					if ($value !== '' && str_contains($value, '@')) {
						[$local, $domain] = explode('@', $value, 2);
						$this->_components['local'] = $local;
						$this->_components['domain'] = strtolower($domain);
					} else if ($value === '') {
						$this->_components['local'] = '';
						$this->_components['domain'] = '';
					} else {
						// no '@' — treat the whole thing as the local part so callers
						// setting partial data don't lose it silently
						$this->_components['local'] = $value;
						$this->_components['domain'] = '';
					}
					return $this;

				case 'local':
					$this->_components['local'] = trim((string)$value);
					return $this;

				case 'domain':
					$this->_components['domain'] = strtolower(trim((string)$value));
					return $this;

				case 'name':
					// Strip CRLF / tabs so the value is safe for mail headers.
					$this->_components['name'] = trim(preg_replace('#[\r\n\t]#', '', (string)$value));
					return $this;
			}

			trigger_error("Unknown email component ($component)", E_USER_WARNING);
			return $this;
		}

		public function __toString() {

			$address = $this->__get('address');

			if ($address === '') {
				return '';
			}

			if (!empty($this->_components['name'])) {
				// Quote the display name if it contains characters that would otherwise
				// break the header (e.g. comma, semicolon). Plain ASCII alphanumeric +
				// space stays unquoted.
				if (preg_match('#[^\w\s\-\.]#u', $this->_components['name'])) {
					return '"'. addcslashes($this->_components['name'], '"\\') .'" <'. $address .'>';
				}
				return $this->_components['name'] .' <'. $address .'>';
			}

			return $address;
		}

		#[\ReturnTypeWillChange] // Fix PHP 8.1
		public function jsonSerialize() {
			return [
				'email' => $this->__get('address'),
				'name'  => $this->_components['name'] ?? '',
			];
		}

		public function reset() {
			$this->_components = [
				'local'  => '',
				'domain' => '',
				'name'   => '',
			];
		}
	}
