<?php

	// ArrayAccess implementation makes it possible to use array-like syntax for accessing entity properties
	class abs_reference_entity implements ArrayAccess {

		protected $_data = [];

		public function &__get($name) {

			if (array_key_exists($name, $this->_data)) {
				return $this->_data[$name];
			}

			$this->_data[$name] = null;

			$this->_load($name);

			return $this->_data[$name];
		}

		public function &__isset($name) {
			return $this->__get($name);
		}

		public function __set($name, $value) {

			if (isset($this->_data[$name])) {
				trigger_error('Overwriting data is prohibited ('.$name.')', E_USER_WARNING);
				return;
			}

			$this->_data[$name] = $value;
		}

    // ArrayAccess implementation
    public function offsetExists($offset): bool {
			return isset($this->data[$offset]);
    }

    public function offsetGet($offset): mixed {
			return fallback($this->data[$offset]);
    }

    public function offsetSet($offset, $value): void {
			if (isset($this->_data[$name])) {
				trigger_error('Overwriting data is prohibited ('.$name.')', E_USER_WARNING);
				return;
			}
			$this->data[$offset] = $value;
    }

    public function offsetUnset($offset): void {
			if (isset($this->_data[$name])) {
				trigger_error('Unsetting data is prohibited ('.$name.')', E_USER_WARNING);
				return;
			}
			//unset($this->data[$offset]);
    }
	}
