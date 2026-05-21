<?php

	// ArrayAccess implementation makes it possible to use array-like syntax for accessing entity properties
	class abs_reference_entity implements ArrayAccess {

		protected $_data = [];

		public function &__get(string $name): mixed {

			if (array_key_exists($name, $this->_data)) {
				return $this->_data[$name];
			}

			$this->_data[$name] = null;

			$this->_load($name);

			return $this->_data[$name];
		}

		public function __isset(string $name): bool {
			if ($this->__get($name) === null) {
				$this->_data[$name] = null;
			}
			return $this->_data[$name] !== null;
		}

		public function __set(string $name, mixed $value) {

			if (isset($this->_data[$name])) {
				trigger_error('Overwriting data is prohibited ('.$name.')', E_USER_WARNING);
				return;
			}

			$this->_data[$name] = $value;
		}

		// ArrayAccess implementation
		public function offsetExists(mixed $offset): bool {
			return isset($this->_data[$offset]);
		}

		public function offsetGet(mixed $offset): mixed {
			return $this->_data[$offset] ?? null;
		}

		public function offsetSet(mixed $offset, mixed $value): void {
			if (isset($this->_data[$offset])) {
				trigger_error('Overwriting data is prohibited ('.$offset.')', E_USER_WARNING);
				return;
			}
			$this->_data[$offset] = $value;
		}

		public function offsetUnset(mixed $offset): void {
			if (isset($this->_data[$offset])) {
				trigger_error('Unsetting data is prohibited ('.$offset.')', E_USER_WARNING);
				return;
			}
		}
	}
