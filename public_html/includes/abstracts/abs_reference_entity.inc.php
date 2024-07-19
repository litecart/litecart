<?php

	class abs_reference_entity {

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
	}
